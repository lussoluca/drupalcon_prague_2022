<?php

namespace Drupal\tracer\Http;

use Psr\Http\Message\RequestInterface;

/**
 * HTTP client middleware to trace requests.
 */
class HttpClientMiddleware {

  /**
   * Middleware callback.
   */
  public function __invoke(): callable {
    return function ($handler): callable {
      return function (RequestInterface $request, array $options) use ($handler) {
        $tracer = \Drupal::service('tracer.tracer');
        $span = $tracer->start('HTTP call', (string) $request->getUri());
        $response = $handler($request, $options);
        $tracer->stop($span);

        return $response;
      };
    };
  }

}
