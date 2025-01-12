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

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;

class ChannelExtension extends AbstractExtension implements GlobalsInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('integrated_channel', $this->getChannel(...)),
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
        return $this->container->get('doctrine_mongodb')->getRepository(Channel::class)->find($id);
    }

    private function getChannelFromContext(): ?ChannelInterface
    {
        $context = $this->container->get('channel.context');

        if (!$context instanceof ChannelContextInterface) {
            throw new \RuntimeException('Unable to get channel context.');
        }

        return $context->getChannel();
    }
}
