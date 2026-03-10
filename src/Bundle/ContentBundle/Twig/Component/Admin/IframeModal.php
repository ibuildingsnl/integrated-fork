<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:iframe_modal', template: '@IntegratedContent/components/admin/iframe_modal.html.twig')]
final class IframeModal
{
    public string $modalId = '';

    public string $title = '';

    public ?string $titleHtml = null;

    public string $iframeId = '';

    public string $iframeSrc = '';

    public ?string $modalClass = null;

    public ?string $dialogClass = null;

    public ?string $iframeClass = null;

    public ?string $iframeAttributes = null;

    public string $closeLabel = 'Close';

    public function modalClasses(): string
    {
        $classes = ['modal', 'add-modal', 'close-outside'];

        if ($this->modalClass) {
            $classes[] = trim($this->modalClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function dialogClasses(): string
    {
        $classes = ['modal-dialog'];

        if ($this->dialogClass) {
            $classes[] = trim($this->dialogClass);
        }

        return implode(' ', array_filter($classes));
    }
}
