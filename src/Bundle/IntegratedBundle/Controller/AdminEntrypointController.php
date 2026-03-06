<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\IntegratedBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class AdminEntrypointController extends AbstractController
{
    public function index(): Response
    {
        try {
            return $this->redirect($this->generateUrl('integrated_dashboard_index'));
        } catch (RouteNotFoundException) {
            return $this->redirect($this->generateUrl('integrated_content_content_index'));
        }
    }
}
