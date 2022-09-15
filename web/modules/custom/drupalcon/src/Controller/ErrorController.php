<?php

namespace Drupal\drupalcon\Controller;

use Drupal\Core\Controller\ControllerBase;

class ErrorController extends ControllerBase {

  public function view() {
    \Drupal::logger('drupalcon')->error('ErrorController::view()');

    return [
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
