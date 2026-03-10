<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:pagination_footer', template: '@IntegratedContent/components/admin/pagination_footer.html.twig')]
final class PaginationFooter
{
    public mixed $pagination = null;

    public string $template = '@KnpPaginator/Pagination/sliding.html.twig';

    public ?string $wrapperClass = null;

    public function hasWrapper(): bool
    {
        return null !== $this->wrapperClass && '' !== trim($this->wrapperClass);
    }
}
