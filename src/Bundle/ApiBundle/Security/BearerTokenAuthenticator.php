<?php

namespace Integrated\Bundle\ApiBundle\Security;

use Integrated\Bundle\ApiBundle\JsonApi\ErrorResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PostAuthenticationToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly AccessTokenIntrospectorInterface $introspector,
        private readonly ErrorResponseFactory $errorResponseFactory
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/api/admin/');
    }

    public function authenticate(Request $request): Passport
    {
        $authorization = (string) $request->headers->get('Authorization', '');

        if (!preg_match('/^Bearer\\s+(.+)$/i', $authorization, $matches)) {
            throw new AuthenticationException('Missing bearer token.');
        }

        $introspection = $this->introspector->introspect(trim($matches[1]));
        if (!$introspection->isActive()) {
            throw new AuthenticationException('Invalid access token.');
        }

        return new SelfValidatingPassport(
            new UserBadge($introspection->getSubject(), static function () use ($introspection): ApiClientUser {
                return new ApiClientUser($introspection->getSubject(), $introspection->getScopes());
            })
        );
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $user = $passport->getUser();
        $roles = ['ROLE_API'];

        if ($user instanceof ApiClientUser) {
            foreach ($user->getScopes() as $scope) {
                $roles[] = 'SCOPE_'.$scope;
            }
        }

        return new PostAuthenticationToken($user, $firewallName, array_values(array_unique($roles)));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->errorResponseFactory->fromException($exception);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->errorResponseFactory->fromException($authException ?? new AuthenticationException('Authentication required.'));
    }
}
