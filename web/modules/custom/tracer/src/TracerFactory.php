<?php

declare(strict_types=1);

namespace Drupal\tracer;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Site\Settings;

/**
 * Factory service to create new Tracer objects.
 */
class TracerFactory {

  /**
   * A Tracer instance.
   *
   * @var \Drupal\tracer\TracerInterface
   */
  private TracerInterface $tracer;

  /**
   * TracerFactory constructor.
   *
   * @param \Drupal\tracer\TracerPluginManager $tracerPluginManager
   *   The Tracer plugin manager.
   * @param \Drupal\Core\Site\Settings $settings
   *   The site settings.
   */
  public function __construct(
    protected readonly TracerPluginManager $tracerPluginManager,
    Settings $settings
  ) {
    $tracerPlugin = $settings->get('tracer_plugin', NULL);

    try {
      if ($tracerPlugin === NULL) {
        $this->tracer = new NoopTracer();
      }

      /** @var \Drupal\tracer\TracerInterface $tracer */
      $tracer = $this->tracerPluginManager->createInstance($tracerPlugin);
      $this->tracer = $tracer;
    }
    catch (PluginException $e) {
      $this->tracer = new NoopTracer();
    }
  }

  /**
   * Return the Tracer instance.
   *
   * @return \Drupal\tracer\TracerInterface
   *   The Tracer instance.
   */
  public function getTracer(): TracerInterface {
    return $this->tracer;
  }

  /**
   * Return a list of traced events.
   *
   * @return array
   *   A list of traced events.
   */
  public function getEvents(): array {
    return $this->tracer->getEvents();
  }

}
