<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:empty_state_message', template: '@IntegratedContent/components/admin/empty_state_message.html.twig')]
final class EmptyStateMessage
{
    public string $tag = 'p';

    public string $variant = 'muted';

    public ?string $message = null;

    public ?string $elementAttributes = null;

    public ?string $wrapperClass = null;

    public ?string $extraClass = null;

    public function tagName(): string
    {
        return \in_array($this->tag, ['div', 'li', 'p', 'span'], true) ? $this->tag : 'p';
    }

    public function classes(): string
    {
        $classes = ['empty-state-message', 'empty-state-message-'.$this->variant];

        if ($this->wrapperClass) {
            $classes[] = trim($this->wrapperClass);
        }

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
