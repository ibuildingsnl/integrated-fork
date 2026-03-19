<?php

namespace Integrated\Bundle\BrandBundle\Solr\Extension;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;

class BrandExtension implements TypeExtensionInterface
{
    public function __construct(
        private readonly BrandRepository $brands,
    ) {
    }

    public function build(ContainerInterface $container, $data, array $options = []): void
    {
        if (!$data instanceof Content) {
            return;
        }

        $none = true;
        $indexedWebsiteChannels = [];

        foreach ($this->brands->all() as $brand) {
            if ($brand->hasPublished($data)) {
                $none = false;
                $container->add('facet_brands', $brand->getId());
                $this->addWebsiteChannelPresentation(
                    $container,
                    $brand->getWebsiteChannel(),
                    $brand->getName(),
                    $brand->getProfile()?->getFavicon()?->getFile()?->getPathname(),
                    $indexedWebsiteChannels
                );
            }
        }

        if ($none) {
            $container->add('facet_brands', 'None');
        }

        foreach ($data->getChannels() as $channel) {
            if (!$channel instanceof Channel || $channel->getType()?->getId() !== 'website') {
                continue;
            }

            $this->addWebsiteChannelPresentation(
                $container,
                $channel,
                $channel->getName() ?: $channel->getId(),
                null,
                $indexedWebsiteChannels
            );
        }
    }

    public function getName(): string
    {
        return 'integrated.content';
    }

    /**
     * @param array<string, true> $indexedWebsiteChannels
     */
    private function addWebsiteChannelPresentation(
        ContainerInterface $container,
        ?Channel $channel,
        string $name,
        ?string $faviconPath,
        array &$indexedWebsiteChannels,
    ): void {
        if (!$channel instanceof Channel) {
            return;
        }

        $channelId = trim($channel->getId());
        if ($channelId === '' || isset($indexedWebsiteChannels[$channelId])) {
            return;
        }

        $indexedWebsiteChannels[$channelId] = true;
        $container->add('website_channel_ids', $channelId);
        $container->add('website_channel_names', trim($name) !== '' ? $name : $channelId);
        $container->add('website_channel_favicon_paths', $faviconPath ?? '');
    }
}
