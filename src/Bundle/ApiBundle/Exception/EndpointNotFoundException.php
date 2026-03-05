<?php

namespace Integrated\Bundle\ApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EndpointNotFoundException extends NotFoundHttpException
{
    public function __construct(string $contract, string $version, string $resource, string $operation)
    {
        parent::__construct(sprintf('No API endpoint is registered for %s/%s resource "%s" operation "%s".', $contract, $version, $resource, $operation));
    }
}
