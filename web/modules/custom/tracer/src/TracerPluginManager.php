<?php

declare(strict_types=1);

namespace Drupal\tracer;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\tracer\Annotation\Tracer;

/**
 * Plugin manager for Tracer plugins.
 */
class TracerPluginManager extends DefaultPluginManager {

  /**
   * TracerPluginManager constructor.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Tracer', $namespaces, $module_handler, TracerInterface::class, Tracer::class);
    $this->setCacheBackend($cache_backend, 'tracer_plugins');
    $this->alterInfo('tracer_info');
  }

}
