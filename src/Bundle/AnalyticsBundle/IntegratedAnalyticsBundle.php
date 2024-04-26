<?php

namespace Integrated\Bundle\AnalyticsBundle;

use Integrated\Bundle\AnalyticsBundle\Infrastructure\IntegratedAnalyticsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IntegratedAnalyticsBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new IntegratedAnalyticsExtension();
    }
}
