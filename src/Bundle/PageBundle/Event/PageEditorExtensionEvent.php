<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Event;

use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\EventDispatcher\Event;

class PageEditorExtensionEvent extends Event
{
    public const SIDEBAR = 'integrated.page.editor.sidebar';

    /**
     * @var array<int, array{html: string, priority: int, position: int}>
     */
    private array $panels = [];

    private int $position = 0;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly AbstractPage $page,
        private readonly ?FormView $form = null,
        private readonly array $context = [],
    ) {
    }

    public function getPage(): AbstractPage
    {
        return $this->page;
    }

    public function getForm(): ?FormView
    {
        return $this->form;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function addPanel(string $html, int $priority = 0): void
    {
        if ('' === trim($html)) {
            return;
        }

        $this->panels[] = [
            'html' => $html,
            'priority' => $priority,
            'position' => $this->position++,
        ];
    }

    /**
     * @return list<string>
     */
    public function getPanels(): array
    {
        $panels = $this->panels;

        usort($panels, static function (array $left, array $right): int {
            return ($right['priority'] <=> $left['priority']) ?: ($left['position'] <=> $right['position']);
        });

        return array_column($panels, 'html');
    }

    public function renderPanels(): string
    {
        return implode("\n", $this->getPanels());
    }
}
