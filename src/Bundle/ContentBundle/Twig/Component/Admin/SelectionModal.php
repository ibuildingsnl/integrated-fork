<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:selection_modal', template: '@IntegratedContent/components/admin/selection_modal.html.twig')]
final class SelectionModal
{
    public string $modalId = '';

    public string $title = '';

    public ?string $titleHtml = null;

    public ?string $bodyHtml = null;

    public ?string $footerHtml = null;

    public ?string $modalClass = null;

    public ?string $dialogClass = null;

    public ?string $bodyClass = null;

    public ?string $modalAttributes = null;

    public bool $showCloseButton = true;

    public ?string $closeButtonAttributes = null;

    public string $closeLabel = 'Close';

    public function modalClasses(): string
    {
        $classes = ['modal', 'add-modal', 'close-outside'];

        if ($this->modalClass) {
            return trim($this->modalClass);
        }

        return implode(' ', $classes);
    }

    public function dialogClasses(): string
    {
        $classes = ['modal-dialog'];

        if ($this->dialogClass) {
            $classes[] = trim($this->dialogClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function bodyClasses(): string
    {
        $classes = ['modal-body'];

        if ($this->bodyClass) {
            $classes[] = trim($this->bodyClass);
        }

        return implode(' ', array_filter($classes));
    }
}
