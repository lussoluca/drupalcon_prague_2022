<?php

declare(strict_types=1);

namespace Drupal\tracer\DependencyInjection;

use Drupal\Component\Utility\Timer;
use Drupal\Core\DependencyInjection\Container;
use Drupal\tracer\TracerFactory;
use Drupal\tracer\TracerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends the Drupal container class to trace service instantiations.
 */
class TraceableContainer extends Container {

  /**
   * Store timing data about services instantiation.
   *
   * @var array
   */
  protected array $tracedData;

  /**
   * The Tracer factory.
   *
   * @var \Drupal\tracer\TracerFactory|null
   */
  private ?TracerFactory $tracerFactory = NULL;

  /**
   * The Tracer instance.
   *
   * @var \Drupal\tracer\TracerInterface|null
   */
  private ?TracerInterface $tracer = NULL;

  /**
   * {@inheritdoc}
   */
  public function get($id, $invalid_behavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object {
    if (
      !$this->tracerFactory &&
      $this->has('tracer.tracer_factory') &&
      !array_key_exists('tracer.tracer_factory', $this->loading)
    ) {
      $this->tracerFactory = parent::get('tracer.tracer_factory');
      $this->tracer = $this->tracerFactory->getTracer();
    }

    if ('tracer.tracer_factory' === $id) {
      return $this->tracerFactory;
    }

    Timer::start($id);

    if ($this->tracer !== NULL) {
      $span = $this->tracer->start('service', $id);
    }

    $service = parent::get($id, $invalid_behavior);

    $this->tracedData[$id] = Timer::stop($id);

    $this->tracer?->stop($span);

    return $service;
  }

  /**
   * Return the traced data.
   *
   * @return array
   *   The traced data.
   */
  public function getTracedData(): array {
    return $this->tracedData;
  }

}
