<?php

namespace Integrated\Bundle\DashboardBundle;

use Integrated\Bundle\DashboardBundle\Infrastructure\IntegratedDashboardExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IntegratedDashboardBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new IntegratedDashboardExtension();
    }
}
