<?php

declare(strict_types=1);

namespace Drupal\tracer\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * Controller resolver that traces time spent.
 */
class TraceableControllerResolver implements ControllerResolverInterface {

  /**
   * TraceableControllerResolver constructor.
   *
   * @param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $resolver
   *   The resolver to wrap.
   */
  public function __construct(
    protected readonly ControllerResolverInterface $resolver) {
  }

  /**
   * {@inheritdoc}
   */
  public function getController(Request $request): callable|FALSE {
    /** @var \Drupal\tracer\TracerInterface $tracer */
    $tracer = \Drupal::service('tracer.tracer');

    $span = $tracer->start('get_controller', $request->getUri());

    $ret = $this->resolver->getController($request);

    $tracer->stop($span);

    return $ret;
  }

}
