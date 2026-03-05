<?php

namespace Integrated\Bundle\ApiBundle\Controller\Admin;

use Integrated\Bundle\ApiBundle\JsonApi\DocumentResponseFactory;
use Symfony\Component\HttpFoundation\Response;

class PingController
{
    public function __construct(private readonly DocumentResponseFactory $documentResponseFactory)
    {
    }

    public function __invoke(): Response
    {
        return $this->documentResponseFactory->create([
            'data' => [
                'type' => 'status',
                'id' => 'admin-v1',
                'attributes' => [
                    'status' => 'ok',
                    'contract' => 'admin',
                    'version' => 'v1',
                ],
            ],
        ]);
    }
}
