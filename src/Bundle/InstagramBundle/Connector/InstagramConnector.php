<?php

namespace Integrated\Bundle\InstagramBundle\Connector;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class InstagramConnector implements ConnectorInterface
{
    public const NAME = 'instagram';

    private readonly DocumentRepository $documentRepository;

    public function __construct(
        private readonly InstagramClient $client,
        DocumentManager $documentManager,
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
            $urls[] = "https://{$domain}{$image->getFile()}";
        }

        return $this->client->postToPage(
            $options['page_token'],
            $options['ig_account'],
            $urls,
            $settings['caption']
        );
    }
}
