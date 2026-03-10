<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:confirm_modal', template: '@IntegratedContent/components/admin/confirm_modal.html.twig')]
final class ConfirmModal
{
    public string $modalId = '';

    public string $title = '';

    public ?string $titleHtml = null;

    public ?string $bodyHtml = null;

    public ?string $cancelHtml = null;

    public ?string $confirmHtml = null;

    public ?string $modalClass = null;

    public ?string $dialogClass = null;

    public ?string $modalAttributes = null;

    public function modalClasses(): string
    {
        $classes = ['modal', 'fade'];

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
