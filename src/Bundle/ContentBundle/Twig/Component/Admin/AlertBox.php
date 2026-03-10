<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:alert_box', template: '@IntegratedContent/components/admin/alert_box.html.twig')]
final class AlertBox
{
    public string $tag = 'div';

    public string $variant = 'info';

    public bool $dismissible = false;

    public ?string $bodyHtml = null;

    public ?string $extraClass = null;

    public string $closeLabel = 'Close notification';

    public function tagName(): string
    {
        return \in_array($this->tag, ['div', 'ul'], true) ? $this->tag : 'div';
    }

    public function classes(): string
    {
        $classes = ['alert', 'alert-'.$this->variant];

        if ($this->dismissible) {
            $classes[] = 'alert-dismissible';
        }

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
