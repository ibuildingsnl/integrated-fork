<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Routing;

use Integrated\Bundle\ContentBundle\JsonLD\UrlGenerator as JsonLdUrlGenerator;
use Symfony\Component\Routing\RouterInterface;

/**
 * Backward-compatible adapter used by legacy routing.services.xml wiring.
 */
class UrlGenerator extends JsonLdUrlGenerator
{
    public function __construct(private readonly ?RouterInterface $router = null)
    {
    }
}

