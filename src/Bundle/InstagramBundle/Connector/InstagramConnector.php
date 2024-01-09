<?php

namespace Integrated\Bundle\InstagramBundle\Connector;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

class InstagramConnector implements ConnectorInterface
{
    public const NAME = 'instagram';
    public const DIMENSION = 2048;

    private readonly DocumentRepository $documentRepository;

    public function __construct(
        private readonly InstagramClient $client,
        DocumentManager $documentManager,
        private readonly ImageExtension $imageExtension,
    )
    {
        $this->documentRepository = $documentManager->getRepository(File::class);
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        if (!$options->has('page_token') && !$options->has('user_token') && !$options->has('ig_account')) {
            throw new CouldNotPublish('An access token and secret are required to create an Instagram exporter');
        }

        if (!($content instanceof Article)) {
            throw new CouldNotPublish('Content is not an Article');
        }

        $imageIds = [];

        foreach ($settings['images'] as $image) {
            $imageIds[] = $image['$id'];
        }

        $images = $this->documentRepository->createQueryBuilder()
            ->field('id')
            ->in($imageIds)
            ->getQuery()
            ->getIterator()
            ->toArray();

        $domain = $content->getPrimaryChannel()->getPrimaryDomain();

        $urls = [];

        foreach ($images as $image) {
            $editedImage = $this->imageExtension->image($image->getFile())
                ->cropResize(self::DIMENSION, self::DIMENSION)
                ->jpeg();
            $urls[] = "https://{$domain}{$editedImage}";
        }

        return $this->client->postToPage(
            $options['page_token'],
            $options['ig_account'],
            $urls,
            $settings['caption']
        );
    }
}
