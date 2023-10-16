<?php

namespace Integrated\Bundle\AnalyticsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\IntegratedAnalyticsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class IntegratedAnalyticsBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new IntegratedAnalyticsExtension();
    }
}
