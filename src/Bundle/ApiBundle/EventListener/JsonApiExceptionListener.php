<?php

namespace Integrated\Bundle\ApiBundle\EventListener;

use Integrated\Bundle\ApiBundle\JsonApi\ErrorResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonApiExceptionListener implements EventSubscriberInterface
{
    public function __construct(private readonly ErrorResponseFactory $errorResponseFactory)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $event->setResponse($this->errorResponseFactory->fromException($event->getThrowable()));
    }
}
