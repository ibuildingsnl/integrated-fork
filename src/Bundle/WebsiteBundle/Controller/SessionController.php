<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;

class SessionController extends AbstractController
{
    public function __construct(
        private readonly ?UriSigner $uriSigner = null
    ) {
    }

    public function enterSession(string $sessionId, Request $request): Response
    {
        if (!$this->isValidSignedRequest($request)) {
            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $sessionId = trim($sessionId);
        if ($sessionId === '' || preg_match('/[^a-zA-Z0-9,-]/', $sessionId)) {
            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $response = new RedirectResponse($this->normalizeInternalPath($request->get('path', '/')));
        $response->headers->setCookie(Cookie::create('PHPSESSID', $sessionId));

        return $response;
    }

    private function normalizeInternalPath(mixed $path): string
    {
        $path = trim((string) $path);

        if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/';
        }

        return $path;
    }

    private function isValidSignedRequest(Request $request): bool
    {
        return $this->uriSigner !== null && $this->uriSigner->checkRequest($request);
    }
}
