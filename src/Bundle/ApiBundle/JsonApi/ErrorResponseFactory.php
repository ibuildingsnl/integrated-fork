<?php

namespace Integrated\Bundle\ApiBundle\JsonApi;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ErrorResponseFactory
{
    public function __construct(private readonly DocumentResponseFactory $documentResponseFactory)
    {
    }

    public function fromException(\Throwable $exception): Response
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof AuthenticationException) {
            $status = Response::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof AccessDeniedException) {
            $status = Response::HTTP_FORBIDDEN;
        } elseif ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
        }

        $detail = $status >= 500 ? 'An unexpected API error occurred.' : $exception->getMessage();

        return $this->documentResponseFactory->create([
            'errors' => [[
                'status' => (string) $status,
                'title' => Response::$statusTexts[$status] ?? 'Error',
                'detail' => $detail,
                'meta' => [
                    'trace_id' => bin2hex(random_bytes(8)),
                ],
            ]],
        ], $status);
    }
}
