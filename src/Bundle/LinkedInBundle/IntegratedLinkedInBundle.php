<?php

namespace Integrated\Bundle\LinkedInBundle;

use Integrated\Bundle\LinkedInBundle\DependencyInjection\IntegratedLinkedInExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IntegratedLinkedInBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new IntegratedLinkedInExtension();
    }
}
