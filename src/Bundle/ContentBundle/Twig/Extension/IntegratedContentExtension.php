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

use Integrated\Bundle\ContentBundle\Event\ContentRenderEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Michael Jongman <michael@e-active.nl>
 * @author Patrick Mestebeld <patrick@e-active.nl>
 */
class IntegratedContentExtension extends AbstractExtension
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('integrated_content', $this->integratedContent(...), ['is_safe' => ['html']]),
        ];
    }

    public function integratedContent(?string $content): string
    {
        if ($content === null) {
            return '';
        } else {
            return $this->eventDispatcher
                ->dispatch(new ContentRenderEvent($content), ContentRenderEvent::NAME)
                ->getContent();
        }
    }

    public function getName(): string
    {
        return 'integrated_content_integrated_content_extension';
    }
}
