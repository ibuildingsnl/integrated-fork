<?php

namespace Integrated\Bundle\ApiBundle\Controller;

use Integrated\Bundle\ApiBundle\Exception\EndpointNotFoundException;
use Integrated\Bundle\ApiBundle\JsonApi\DocumentResponseFactory;
use Integrated\Bundle\ApiBundle\Registry\EndpointRegistry;
use Integrated\Bundle\ApiBundle\Security\ScopeAuthorizationChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DispatchController extends AbstractController
{
    public function __construct(
        private readonly EndpointRegistry $endpointRegistry,
        private readonly ScopeAuthorizationChecker $scopeAuthorizationChecker,
        private readonly DocumentResponseFactory $documentResponseFactory
    ) {
    }

    public function collection(Request $request): Response
    {
        return $this->dispatch($request, null);
    }

    public function item(Request $request, string $id): Response
    {
        return $this->dispatch($request, $id);
    }

    private function dispatch(Request $request, ?string $id): Response
    {
        $contract = (string) $request->attributes->get('contract');
        $version = (string) $request->attributes->get('version');
        $resource = (string) $request->attributes->get('resource');
        $operation = $this->resolveOperation($request, $id);

        $definition = $this->endpointRegistry->get($contract, $version, $resource, $operation);
        if (!$definition) {
            throw new EndpointNotFoundException($contract, $version, $resource, $operation);
        }

        if ($contract === 'admin') {
            $this->scopeAuthorizationChecker->assertScopes($definition->getScopes());
        }

        $result = $definition->getHandler()->handle($request, $id);

        if ($result instanceof Response) {
            if (!$result->headers->has('Content-Type')) {
                $result->headers->set('Content-Type', 'application/vnd.api+json');
            }

            return $result;
        }

        if (null === $result) {
            return new Response('', Response::HTTP_NO_CONTENT, ['Content-Type' => 'application/vnd.api+json']);
        }

        $status = $operation === 'create' ? Response::HTTP_CREATED : Response::HTTP_OK;

        return $this->documentResponseFactory->create($result, $status);
    }

    private function resolveOperation(Request $request, ?string $id): string
    {
        $method = strtoupper($request->getMethod());

        if (null === $id) {
            return match ($method) {
                'GET' => 'list',
                'POST' => 'create',
                default => throw $this->createNotFoundException('Unsupported collection operation.'),
            };
        }

        return match ($method) {
            'GET' => 'get',
            'PATCH' => 'update',
            'DELETE' => 'delete',
            default => throw $this->createNotFoundException('Unsupported item operation.'),
        };
    }
}
