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

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

class ChannelExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly ChannelContextInterface $context,
    ) {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('integrated_channel', [$this, 'getChannel']),
        ];
    }

    public function getGlobals(): array
    {
        return [
            '_channel' => $this->getChannelFromContext(),
        ];
    }

    public function getChannel(string $id): ?ChannelInterface
    {
        return $this->channelRepository->find($id);
    }

    private function getChannelFromContext(): ?ChannelInterface
    {
        return $this->context->getChannel();
    }
}
