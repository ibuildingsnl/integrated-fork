<?php

namespace Integrated\Bundle\ApiBundle\Registry;

use Integrated\Bundle\ApiBundle\Endpoint\EndpointHandlerInterface;

class EndpointDefinition
{
    /**
     * @param string[] $scopes
     */
    public function __construct(
        private readonly string $contract,
        private readonly string $version,
        private readonly string $resource,
        private readonly string $operation,
        private readonly EndpointHandlerInterface $handler,
        private readonly array $scopes = []
    ) {
    }

    public function getContract(): string
    {
        return $this->contract;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getHandler(): EndpointHandlerInterface
    {
        return $this->handler;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
