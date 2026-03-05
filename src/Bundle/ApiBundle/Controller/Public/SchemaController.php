<?php

namespace Integrated\Bundle\ApiBundle\Controller\Public;

use Integrated\Bundle\ApiBundle\JsonApi\DocumentResponseFactory;
use Integrated\Bundle\ApiBundle\Schema\SchemaBuilder;
use Symfony\Component\HttpFoundation\Response;

class SchemaController
{
    public function __construct(
        private readonly SchemaBuilder $schemaBuilder,
        private readonly DocumentResponseFactory $documentResponseFactory
    ) {
    }

    public function __invoke(): Response
    {
        return $this->documentResponseFactory->create([
            'data' => [
                'type' => 'schema',
                'id' => 'public-v1',
                'attributes' => $this->schemaBuilder->build('public', 'v1'),
            ],
        ]);
    }
}
