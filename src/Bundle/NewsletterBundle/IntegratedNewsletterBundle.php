<?php

namespace Integrated\Bundle\NewsletterBundle;

use Integrated\Bundle\NewsletterBundle\DependencyInjection\IntegratedNewsletterExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IntegratedNewsletterBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new IntegratedNewsletterExtension();
    }
}
