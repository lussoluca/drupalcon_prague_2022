<?php

declare(strict_types=1);

namespace Drupal\tracer\Twig\Extension;

use Drupal\tracer\TracerInterface;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

/**
 * Twig extension to trace Twig templates.
 */
class TraceableProfilerExtension extends ProfilerExtension {

  /**
   * The Tracer instance.
   *
   * @var \Drupal\tracer\TracerInterface
   */
  private TracerInterface $tracer;

  /**
   * Traced events.
   *
   * @var \SplObjectStorage
   */
  private \SplObjectStorage $events;

  /**
   * TraceableProfilerExtension constructor.
   *
   * @param \Twig\Profiler\Profile $profile
   *   The Twig profile.
   */
  public function __construct(Profile $profile) {
    parent::__construct($profile);

    $this->tracer = \Drupal::service('tracer.tracer');
    $this->events = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function enter(Profile $profile) {
    if ($profile->isTemplate()) {
      $this->events[$profile] = $this->tracer->start('Twig', $profile->getName());
    }

    parent::enter($profile);
  }

  /**
   * {@inheritdoc}
   */
  public function leave(Profile $profile) {
    parent::leave($profile);

    if ($profile->isTemplate()) {
      $this->tracer->stop($this->events[$profile]);
      unset($this->events[$profile]);
    }
  }

}
