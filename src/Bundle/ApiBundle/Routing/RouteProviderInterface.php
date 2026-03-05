<?php

namespace Integrated\Bundle\ApiBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

interface RouteProviderInterface
{
    public function provideRoutes(): RouteCollection;
}
