<?php

namespace Integrated\Bundle\ApiBundle\Security;

interface AccessTokenIntrospectorInterface
{
    public function introspect(string $token): TokenIntrospectionResult;
}
