<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:edit_form_shell', template: '@IntegratedContent/components/admin/edit_form_shell.html.twig')]
final class EditFormShell
{
    public ?string $extraClass = null;

    public ?string $toolbarHtml = null;

    public ?string $sidebarContentHtml = null;

    public ?string $editorWrapperClass = null;

    public ?string $pageTitleHtml = null;

    public ?string $editorContentHtml = null;

    public ?string $editorClass = null;

    public function shellClasses(): string
    {
        $classes = ['flex', 'flex-wrap', 'edit-form'];

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function editorWrapperClasses(): string
    {
        $classes = ['editor-wrapper'];

        if ($this->editorWrapperClass) {
            $classes[] = trim($this->editorWrapperClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function editorClasses(): string
    {
        $classes = ['editor', 'editor-wrapped'];

        if ($this->editorClass) {
            $classes[] = trim($this->editorClass);
        }

        return implode(' ', array_filter($classes));
    }
}
