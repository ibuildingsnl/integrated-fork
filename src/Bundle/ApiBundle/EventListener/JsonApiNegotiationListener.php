<?php

namespace Integrated\Bundle\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonApiNegotiationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 255],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!str_starts_with($path, '/api/')) {
            return;
        }

        $accept = (string) $request->headers->get('Accept', '');
        if ($accept !== '' && !str_contains($accept, 'application/vnd.api+json') && !str_contains($accept, '*/*')) {
            throw new NotAcceptableHttpException('The API only serves application/vnd.api+json responses.');
        }

        if (in_array($request->getMethod(), ['POST', 'PATCH', 'PUT'], true)) {
            $contentType = (string) $request->headers->get('Content-Type', '');
            if (!str_starts_with($contentType, 'application/vnd.api+json')) {
                throw new UnsupportedMediaTypeHttpException('The API only accepts application/vnd.api+json request payloads.');
            }
        }
    }
}
