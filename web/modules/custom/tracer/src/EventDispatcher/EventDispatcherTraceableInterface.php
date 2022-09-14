<?php

namespace Drupal\tracer\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Extends the EventDispatcherInterface to add a traceable interface.
 */
interface EventDispatcherTraceableInterface extends EventDispatcherInterface {

  /**
   * Return an array of all the events that have been dispatched.
   *
   * @return array
   *   An array of all the events that have been dispatched.
   */
  public function getCalledListeners(): array;

  /**
   * Return an array of all the events that have not been dispatched.
   *
   * @return mixed
   *   An array of all the events that have not been dispatched.
   */
  public function getNotCalledListeners(): array;

}
