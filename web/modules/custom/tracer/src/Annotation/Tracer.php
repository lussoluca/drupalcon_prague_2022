<?php

namespace Drupal\tracer\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a Tracer annotation object.
 *
 * @Annotation
 */
class Tracer extends Plugin {

  /**
   * The plugin label.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public Translation $label;

  /**
   * The plugin description.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public Translation $description;

}
