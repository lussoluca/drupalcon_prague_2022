<?php

namespace Drupal\devdays\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MicroserviceController extends ControllerBase {

  /**
   * @var \GuzzleHttp\Client
   */
  private Client $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  final public function __construct(Client $httpClient) {
    $this->httpClient = $httpClient;
  }

  public function view() {
    $response = $this->httpClient->get('https://google.com');

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
    ];
  }

}
