<?php

namespace Drupal\devdays\Controller;

use Drupal\Core\Controller\ControllerBase;

class ErrorController extends ControllerBase {

  public function view() {
    \Drupal::logger('devdays')->error('ErrorController::view()');

    return [
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
