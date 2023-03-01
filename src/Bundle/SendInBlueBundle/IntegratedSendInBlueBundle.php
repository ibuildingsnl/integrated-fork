<?php

namespace Integrated\Bundle\SendInBlueBundle;

use Integrated\Bundle\SendInBlueBundle\DependencyInjection\SendInBlueExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IntegratedSendInBlueBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SendInBlueExtension();
    }
}
