<?php

namespace Drupal\tracer;

/**
 * Interface for tracer backends.
 */
interface TracerInterface {

  /**
   * Start a span.
   *
   * @param string $category
   *   Span category.
   * @param string $name
   *   Span name.
   * @param array $attributes
   *   Span extra attributes.
   *
   * @return object
   *   The new span.
   */
  public function start(string $category, string $name, array $attributes = []): object;

  /**
   * Open a new section.
   *
   * @param object $span
   *   A span instance.
   *
   * @return object
   *   A span instance.
   */
  public function openSection(object $span): object;

  /**
   * Close a section.
   *
   * @param object $span
   *   A span instance.
   *
   * @return object
   *   A span instance.
   */
  public function closeSection(object $span): object;

  /**
   * Stop a span.
   *
   * @param object $span
   *   A span instance.
   */
  public function stop(object $span): void;

  /**
   * Return all traced spans.
   *
   * @return array
   *   All traced spans.
   */
  public function getEvents(): array;

}
