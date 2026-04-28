<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Twig\Extension;

use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Event\PageEditorExtensionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PageEditorExtension extends AbstractExtension
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'integrated_page_editor_sidebar_extensions',
                $this->renderEditorSidebarExtensions(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function renderEditorSidebarExtensions(
        AbstractPage $page,
        ?FormView $form = null,
        array $context = [],
    ): string {
        return $this->eventDispatcher
            ->dispatch(
                new PageEditorExtensionEvent($page, $form, $context),
                PageEditorExtensionEvent::SIDEBAR
            )
            ->renderPanels();
    }
}
