<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:edit_drawer_panel', template: '@IntegratedContent/components/admin/edit_drawer_panel.html.twig')]
final class EditDrawerPanel
{
    public string $holderClass = 'bg-white hide';

    public string $wrapperId = 'editimagewrapper';

    public string $editImagePath = '';

    public string $editImageIframePath = '';

    public string $frameId = 'media-edit-panel';

    public string $mediaId = '';

    public string $file = '';

    public function holderClasses(): string
    {
        $classes = ['aside-edit-holder'];

        if ($this->holderClass !== '') {
            $classes[] = trim($this->holderClass);
        }

        return implode(' ', array_filter($classes));
    }
}
