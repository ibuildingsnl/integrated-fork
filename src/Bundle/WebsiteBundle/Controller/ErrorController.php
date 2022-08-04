<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Exception;
use Integrated\Bundle\ThemeBundle\Exception\CircularFallbackException;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ErrorController as TwigErrorController;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class ErrorController extends AbstractController
{
    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * @var TwigErrorController
     */
    protected $controller;

    /**
     * @param ThemeManager $themeManager
     * @param TwigErrorController $controller
     */
    public function __construct(ThemeManager $themeManager, TwigErrorController $controller)
    {
        $this->themeManager = $themeManager;
        $this->controller = $controller;
    }

    public function show(Request $request, Exception $exception): Response
    {
        $flattened = FlattenException::create($exception);
        try {
            if ($template = $this->themeManager->locateTemplate(sprintf('error/%s.%s.twig', $flattened->getStatusCode(), $request->getPreferredFormat()))) {
                return $this->render($template);
            }
        } catch (CircularFallbackException) {
        }

        return $this->controller->__invoke($exception);
    }
}
