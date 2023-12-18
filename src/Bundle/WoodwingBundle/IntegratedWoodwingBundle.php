<?php

namespace Integrated\Bundle\WoodwingBundle;

use Integrated\Bundle\WoodwingBundle\DependencyInjection\IntegratedWoodwingExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IntegratedWoodwingBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new IntegratedWoodwingExtension();
    }
}
