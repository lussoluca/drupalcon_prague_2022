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

  public function endpoint1() {
    try {
      $response = $this->httpClient->get('http://ddev-drupalcon-prague-2022-microservice:8080/endpoint1');
      $json = json_decode($response->getBody()->getContents());
      $this->getLogger('drupalcon')->notice($json->message);

      $this->someComplexMethod();

      return [
        '#theme' => 'microservice',
        '#message' => $json->message,
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
    catch (\Exception $e) {
      $this->getLogger('drupalcon')->notice($e->getMessage());

      return [
        '#theme' => 'microservice',
        '#message' => $this->t('Some error occurred, please try again later.'),
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
  }

  public function endpoint2() {
    try {
      $response = $this->httpClient->get('http://ddev-drupalcon-prague-2022-microservice:8080/endpoint2');
      $json = json_decode($response->getBody()->getContents());
      $this->getLogger('drupalcon')->notice($json->message);

      return [
        '#theme' => 'microservice',
        '#message' => $json->message,
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
    catch (\Exception $e) {
      $this->getLogger('drupalcon')->notice($e->getMessage());

      return [
        '#theme' => 'microservice',
        '#message' => $this->t('Some error occurred, please try again later.'),
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
