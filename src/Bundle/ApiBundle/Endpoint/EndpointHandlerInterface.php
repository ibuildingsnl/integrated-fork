<?php

namespace Integrated\Bundle\ApiBundle\Endpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface EndpointHandlerInterface
{
    /**
     * @return array<string, mixed>|Response|null
     */
    public function handle(Request $request, ?string $id = null): array|Response|null;
}
