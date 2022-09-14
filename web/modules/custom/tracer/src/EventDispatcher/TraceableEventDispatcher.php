<?php

declare(strict_types=1);

namespace Drupal\tracer\EventDispatcher;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\tracer\TracerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Extends the Symfony event dispatcher to trace events.
 */
class TraceableEventDispatcher extends ContainerAwareEventDispatcher implements EventDispatcherTraceableInterface {

  /**
   * The Tracer instance.
   *
   * @var \Drupal\tracer\TracerInterface
   */
  protected TracerInterface $tracer;

  /**
   * An array of all the events that have been dispatched.
   *
   * @var array
   */
  protected array $calledListeners;

  /**
   * An array of all the events that have not been dispatched.
   *
   * @var array
   */
  protected array $notCalledListeners;

  /**
   * The span used to trace the Controller invocation.
   *
   * @var object|null
   */
  private ?object $controllerSpan;

  /**
   * {@inheritdoc}
   */
  public function __construct(ContainerInterface $container, array $listeners = []) {
    parent::__construct($container, $listeners);

    $this->notCalledListeners = $listeners;
    $this->controllerSpan = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function addListener($event_name, $listener, $priority = 0) {
    parent::addListener($event_name, $listener, $priority);

    $this->notCalledListeners[$event_name][$priority][] = ['callable' => $listener];
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(object $event, ?string $eventName = NULL): object {
    $event_name = $eventName ?? get_class($event);

    $this->beforeDispatch($event_name, $event);

    if (isset($this->listeners[$event_name])) {
      // Sort listeners if necessary.
      if (isset($this->unsorted[$event_name])) {
        krsort($this->listeners[$event_name]);
        unset($this->unsorted[$event_name]);
      }

      // Invoke listeners and resolve callables if necessary.
      foreach ($this->listeners[$event_name] as $priority => &$definitions) {
        foreach ($definitions as &$definition) {
          if (!isset($definition['callable'])) {
            $definition['callable'] = [
              $this->container->get($definition['service'][0]),
              $definition['service'][1],
            ];
          }
          if (is_array($definition['callable']) && isset($definition['callable'][0]) && $definition['callable'][0] instanceof \Closure) {
            $definition['callable'][0] = $definition['callable'][0]();
          }

          $span = $this->tracer->start('event', $event_name, ['priority' => $priority]);
          call_user_func($definition['callable'], $event, $event_name, $this);
          $this->tracer->stop($span);

          $this->addCalledListener($definition, $event_name, $priority);

          if ($event->isPropagationStopped()) {
            return $event;
          }
        }
      }
    }

    $this->afterDispatch($event_name, $event);

    return $event;
  }

  /**
   * {@inheritdoc}
   */
  public function getCalledListeners(): array {
    return $this->calledListeners;
  }

  /**
   * {@inheritdoc}
   */
  public function getNotCalledListeners(): array {
    return $this->notCalledListeners;
  }

  /**
   * Set the Tracer instance.
   *
   * @param \Drupal\tracer\TracerInterface $tracer
   *   The Tracer's instance.
   */
  public function setTracer(TracerInterface $tracer) {
    $this->tracer = $tracer;
  }

  /**
   * Called before dispatching the event.
   *
   * @param string $eventName
   *   The event's name.
   * @param object $event
   *   The event's object.
   */
  protected function beforeDispatch(string $eventName, object $event) {
    switch ($eventName) {
      case KernelEvents::VIEW:
      case KernelEvents::RESPONSE:
        // Stop only if a controller has been executed.
        if ($this->controllerSpan != NULL) {
          $this->tracer->stop($this->controllerSpan);
        }
        break;
    }
  }

  /**
   * Called after dispatching the event.
   *
   * @param string $eventName
   *   The event's name.
   * @param object $event
   *   The event's object.
   */
  protected function afterDispatch(string $eventName, object $event) {
    if ($eventName == KernelEvents::CONTROLLER_ARGUMENTS) {
      $this->controllerSpan = $this->tracer->start('controller', 'todo');
    }
  }

  /**
   * Add listener to the called listeners array.
   *
   * @param array $definition
   *   The event's definition.
   * @param string $event_name
   *   The event's name.
   * @param int $priority
   *   The event's priority.
   */
  private function addCalledListener(array $definition, string $event_name, int $priority) {
    if ($this->isClosure($definition['callable'])) {
      $this->calledListeners[$event_name][$priority][] = [
        'class' => 'Closure',
        'method' => '',
      ];
    }
    else {
      $this->calledListeners[$event_name][$priority][] = [
        'class' => get_class($definition['callable'][0]),
        'method' => $definition['callable'][1],
      ];
    }

    foreach ($this->notCalledListeners[$event_name][$priority] as $key => $listener) {
      if (isset($listener['service'])) {
        if ($listener['service'][0] == $definition['service'][0] && $listener['service'][1] == $definition['service'][1]) {
          unset($this->notCalledListeners[$event_name][$priority][$key]);
        }
      }
      else {
        if ($this->isClosure($listener['callable'])) {
          if (is_callable($listener['callable'], TRUE, $listenerCallableName) && is_callable($definition['callable'], TRUE, $definitionCallableName)) {
            if ($listenerCallableName == $definitionCallableName) {
              unset($this->notCalledListeners[$event_name][$priority][$key]);
            }
          }
        }
        else {
          if (get_class($listener['callable'][0]) == get_class($definition['callable'][0]) && $listener['callable'][1] == $definition['callable'][1]) {
            unset($this->notCalledListeners[$event_name][$priority][$key]);
          }
        }
      }

    }
  }

  /**
   * Returns whether the given callable is a closure.
   *
   * @param callable $t
   *   The callable.
   *
   * @return bool
   *   TRUE if the callable is a closure, FALSE otherwise.
   */
  private function isClosure(callable $t): bool {
    return $t instanceof \Closure;
  }

}
