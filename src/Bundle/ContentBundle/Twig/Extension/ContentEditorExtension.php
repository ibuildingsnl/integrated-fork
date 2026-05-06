<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Integrated\Bundle\ContentBundle\Event\ContentEditorExtensionEvent;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentEditorExtension extends AbstractExtension
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'integrated_content_editor_sidebar_extensions',
                $this->renderEditorSidebarExtensions(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function renderEditorSidebarExtensions(
        ?ContentInterface $content,
        ?ContentTypeInterface $contentType = null,
        array $context = [],
    ): string {
        if ($content === null) {
            return '';
        }

        return $this->eventDispatcher
            ->dispatch(
                new ContentEditorExtensionEvent($content, $contentType, $context),
                ContentEditorExtensionEvent::SIDEBAR
            )
            ->renderPanels();
    }
}
