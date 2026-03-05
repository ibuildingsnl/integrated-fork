<?php

namespace Integrated\Bundle\ApiBundle\Routing;

use Integrated\Bundle\ApiBundle\Controller\DispatchController;
use Integrated\Bundle\ApiBundle\Controller\Public\PingController;
use Integrated\Bundle\ApiBundle\Controller\Public\SchemaController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CorePublicRouteProvider implements RouteProviderInterface
{
    public function provideRoutes(): RouteCollection
    {
        $collection = new RouteCollection();

        $collection->add('integrated_api_public_v1_ping', new Route('/api/public/v1/ping', [
            '_controller' => PingController::class,
        ], [], [], '', [], ['GET']));

        $collection->add('integrated_api_public_v1_schema', new Route('/api/public/v1/schema', [
            '_controller' => SchemaController::class,
        ], [], [], '', [], ['GET']));

        $collection->add('integrated_api_public_v1_resource_list', new Route('/api/public/v1/{resource}', [
            '_controller' => DispatchController::class.'::collection',
            'contract' => 'public',
            'version' => 'v1',
        ], [
            'resource' => '[a-z0-9\\-]+'
        ], [], '', [], ['GET']));

        $collection->add('integrated_api_public_v1_resource_get', new Route('/api/public/v1/{resource}/{id}', [
            '_controller' => DispatchController::class.'::item',
            'contract' => 'public',
            'version' => 'v1',
        ], [
            'resource' => '[a-z0-9\\-]+',
            'id' => '[^/]+'
        ], [], '', [], ['GET']));

        return $collection;
    }
}
