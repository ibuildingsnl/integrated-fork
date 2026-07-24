<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SitemapBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RobotsController
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function index(): Response
    {
        return new Response(
            $this->twig->render('@IntegratedSitemap/robots/index.txt.twig', []),
            200,
            ['Content-Type' => 'text/plain']
        );
    }
}
