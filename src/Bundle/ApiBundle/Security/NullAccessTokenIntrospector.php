<?php

namespace Integrated\Bundle\ApiBundle\Security;

class NullAccessTokenIntrospector implements AccessTokenIntrospectorInterface
{
    public function introspect(string $token): TokenIntrospectionResult
    {
        return new TokenIntrospectionResult(false);
    }
}
