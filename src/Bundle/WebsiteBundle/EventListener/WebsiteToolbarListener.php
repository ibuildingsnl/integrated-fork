<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\WebsiteBundle\Service\EditableChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class WebsiteToolbarListener implements EventSubscriberInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var EditableChecker
     */
    protected $websiteEditableChecker;

    /*
     * @var string
     */
    protected $toolbarMessage = '';

    /*
     * @var Content
     */
    protected $contentItem = null;

    public function __construct(Environment $twig, EditableChecker $websiteEditableChecker)
    {
        $this->twig = $twig;
        $this->websiteEditableChecker = $websiteEditableChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -128]];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->websiteEditableChecker->checkEditable() || $this->contentItem !== null) {
            $this->injectToolbar($event->getResponse());
        }
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function injectToolbar(Response $response)
    {
        $content = $response->getContent();
        $pos = stripos($content, '<body');

        if (false !== $pos) {
            $toolbar = $this->twig->render(
                '@IntegratedWebsite/toolbar.html.twig',
                [
                    'message' => $this->toolbarMessage,
                    'layoutEditable' => $this->websiteEditableChecker->checkEditable(),
                    'content' => $this->contentItem,
                ]
            );

            $end = stripos($content, '>', $pos) + 1;
            $content = substr_replace($content, "\n".$toolbar, $end, 0);

            $response->setContent($content);
        }
    }

    public function setToolbarMessage(string $message)
    {
        $this->toolbarMessage = $message;
    }

    public function setContentItem(Content $content)
    {
        $this->contentItem = $content;
    }
}
