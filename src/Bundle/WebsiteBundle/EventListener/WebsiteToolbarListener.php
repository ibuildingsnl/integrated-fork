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

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\WebsiteBundle\Service\EditableChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class WebsiteToolbarListener implements EventSubscriberInterface
{
    private Environment $twig;
    private EditableChecker $websiteEditableChecker;
    private DocumentManager $manager;

    private string $toolbarMessage = '';

    private ?Content $contentItem = null;

    public function __construct(Environment $twig, EditableChecker $websiteEditableChecker, DocumentManager $manager)
    {
        $this->twig = $twig;
        $this->websiteEditableChecker = $websiteEditableChecker;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -128]];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->websiteEditableChecker->checkEditable() || $this->contentItem !== null) {
            $this->injectToolbar($event->getRequest(), $event->getResponse());
        }
    }

    private function injectToolbar(Request $request, Response $response)
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
                    'page' => $this->manager->getRepository(AbstractPage::class)->find($request->attributes->get('page')),
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
