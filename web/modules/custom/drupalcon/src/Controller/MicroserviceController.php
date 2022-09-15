<?php

namespace Drupal\drupalcon\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MicroserviceController extends ControllerBase {

  private Client $httpClient;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  final public function __construct(Client $httpClient) {
    $this->httpClient = $httpClient;
  }

  public function view() {
    try {
      $response = $this->httpClient->get('http://ddev-drupal10-microservice:8080/hello-instrumented');
      $json = json_decode($response->getBody()->getContents());
      $this->getLogger('drupalcon')->notice($json->message);

      $this->someComplexMethod();

      return [
        '#type' => 'markup',
        '#markup' => $json->message,
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
    catch (\Exception $e) {
      return [
        '#type' => 'markup',
        '#markup' => $e->getMessage(),
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
  }

  private function someComplexMethod() {
    /** @var \Drupal\tracer\TracerInterface $tracer */
    $tracer = \Drupal::service('tracer.tracer');

    $span = $tracer->start(
      'custom',
      'someComplexMethod',
      ['someAttribute' => 'someValue']
    );

    $this->getLogger('drupalcon')->info('someComplexMethod start');
    sleep(1);
    $this->getLogger('drupalcon')->info('someComplexMethod start');

    $tracer->stop($span);
  }

}
