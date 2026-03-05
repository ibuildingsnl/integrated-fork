<?php

namespace Integrated\Bundle\ApiBundle\Routing;

use Integrated\Bundle\ApiBundle\Controller\Admin\PingController;
use Integrated\Bundle\ApiBundle\Controller\Admin\SchemaController;
use Integrated\Bundle\ApiBundle\Controller\DispatchController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CoreAdminRouteProvider implements RouteProviderInterface
{
    public function provideRoutes(): RouteCollection
    {
        $collection = new RouteCollection();

        $collection->add('integrated_api_admin_v1_ping', new Route('/api/admin/v1/ping', [
            '_controller' => PingController::class,
        ], [], [], '', [], ['GET']));

        $collection->add('integrated_api_admin_v1_schema', new Route('/api/admin/v1/schema', [
            '_controller' => SchemaController::class,
        ], [], [], '', [], ['GET']));

        $collection->add('integrated_api_admin_v1_resource_collection', new Route('/api/admin/v1/{resource}', [
            '_controller' => DispatchController::class.'::collection',
            'contract' => 'admin',
            'version' => 'v1',
        ], [
            'resource' => '[a-z0-9\\-]+'
        ], [], '', [], ['GET', 'POST']));

        $collection->add('integrated_api_admin_v1_resource_item', new Route('/api/admin/v1/{resource}/{id}', [
            '_controller' => DispatchController::class.'::item',
            'contract' => 'admin',
            'version' => 'v1',
        ], [
            'resource' => '[a-z0-9\\-]+',
            'id' => '[^/]+'
        ], [], '', [], ['GET', 'PATCH', 'DELETE']));

        return $collection;
    }
}
