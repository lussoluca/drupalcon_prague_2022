<?php

declare(strict_types=1);

namespace Drupal\tracer\StackMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Stack middleware to trace the request.
 */
class TracesMiddleware implements HttpKernelInterface {

  /**
   * The decorated kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected HttpKernelInterface $httpKernel;

  /**
   * Constructs a WebprofilerMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = TRUE): Response {
    /** @var \Drupal\tracer\TracerInterface $tracer */
    $tracer = \Drupal::service('tracer.tracer');
    $rootSpan = $tracer->start('root', 'root');
    $tracer->openSection($rootSpan);

    $response = $this->httpKernel->handle($request, $type, $catch);

    $tracer->stop($rootSpan);
    $tracer->closeSection($rootSpan);

    return $response;
  }

}
