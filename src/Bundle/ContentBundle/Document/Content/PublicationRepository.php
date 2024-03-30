<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Common\Content\Channel\ChannelInterface;

class PublicationRepository extends DocumentRepository implements PublicationRepositoryInterface
{
    public function forContent(Content $content): array
    {
        if (!$content->getId()) {
            return [];
        }

        return $this->findBy(['content' => $content]);
    }

    public function forDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $startMongoDate = new \MongoDB\BSON\UTCDateTime($startDate->getTimestamp() * 1000);
        $endMongoDate = new \MongoDB\BSON\UTCDateTime($endDate->getTimestamp() * 1000);

        $query = [
            'time.startDate' => [
                '$gte' => $startMongoDate,
                '$lte' => $endMongoDate,
            ],
        ];

        return $this->findBy($query);
    }

    public function forContentByChannel(Content $content): array
    {
        return array_combine(
            array_map(fn (Publication $p) => $p->getChannel()->getId(), $this->forContent($content)),
            $this->forContent($content),
        );
    }

    public function forContentOnChannel(Content $content, ChannelInterface $channel): array
    {
        if (!$content->getId()) {
            return [];
        }

        return $this->findBy(['content' => $content, 'channel' => $channel]);
    }

    public function availablePublicationsForChannelByContent(Content $content, ChannelInterface $channel): array
    {
        if (!$content->getId()) {
            return [];
        }

        $failedPublications = $this->findBy(
            [
                'content' => $content,
                'channel' => $channel,
                'status' => 'failed',
            ]
        );

        $nullStatusPublications = $this->findBy(
            [
                'content' => $content,
                'channel' => $channel,
                'status' => '',
            ]
        );

        return array_merge($failedPublications, $nullStatusPublications);
    }

    public function add(Publication $publication): void
    {
        $this->getDocumentManager()->persist($publication);
    }

    public function remove(Publication $publication): void
    {
        $this->getDocumentManager()->remove($publication);
    }
}
