<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Event;

use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ContentEditorExtensionEvent extends Event
{
    public const SIDEBAR = 'integrated.content.editor.sidebar';

    /**
     * @var array<int, array{html: string, priority: int, position: int}>
     */
    private array $panels = [];

    private int $position = 0;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly ContentInterface $content,
        private readonly ?ContentTypeInterface $contentType = null,
        private readonly array $context = [],
    ) {
    }

    public function getContent(): ContentInterface
    {
        return $this->content;
    }

    public function getContentType(): ?ContentTypeInterface
    {
        return $this->contentType;
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
