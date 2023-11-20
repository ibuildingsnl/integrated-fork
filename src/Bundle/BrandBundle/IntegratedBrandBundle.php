<?php

namespace Integrated\Bundle\BrandBundle;

use Integrated\Bundle\BrandBundle\Infrastructure\IntegratedBrandExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IntegratedBrandBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new IntegratedBrandExtension();
    }
}
