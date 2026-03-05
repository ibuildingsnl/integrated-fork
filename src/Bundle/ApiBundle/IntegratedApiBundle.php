<?php

namespace Integrated\Bundle\ApiBundle;

use Integrated\Bundle\ApiBundle\DependencyInjection\Compiler\RegisterEndpointPass;
use Integrated\Bundle\ApiBundle\DependencyInjection\Compiler\RegisterRouteProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IntegratedApiBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterEndpointPass(), \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new RegisterRouteProviderPass(), \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
