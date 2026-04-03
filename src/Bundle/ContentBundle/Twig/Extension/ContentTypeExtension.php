<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ContentTypeExtension extends AbstractExtension
{
    private ResolverInterface $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('integrated_content_type', $this->getContentType(...)),
        ];
    }

    public function getContentType(string $id): ?ContentTypeInterface
    {
        if ($this->resolver->hasType($id)) {
            return $this->resolver->getType($id);
        }

        return null;
    }
}
