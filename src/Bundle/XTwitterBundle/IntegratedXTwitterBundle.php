<?php

namespace Integrated\Bundle\XTwitterBundle;

use Integrated\Bundle\XTwitterBundle\DependencyInjection\IntegratedXTwitterExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IntegratedXTwitterBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new IntegratedXTwitterExtension();
    }
}
