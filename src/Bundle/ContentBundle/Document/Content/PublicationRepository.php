<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Common\Content\Channel\ChannelInterface;

class PublicationRepository extends DocumentRepository implements PublicationRepositoryInterface
{
    public function forContent(Content $content): array
    {
        $contentId = $content->getId();
        if (!\is_string($contentId) || '' === $contentId) {
            return [];
        }

        return $this->findBy(['content' => $content]);
    }

    public function forDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): iterable
    {
        return $this->createQueryBuilder()
            ->setRewindable(false)
            ->field('time.startDate')->gte($startDate)
            ->field('time.startDate')->lte($endDate)
            ->getQuery()
            ->getIterator();
    }

    public function forContentByChannel(Content $content): array
    {
        $publications = $this->forContent($content);

        usort($publications, function (Publication $a, Publication $b): int {
            $aIsSuccessful = $a->getStatus() === Publication::STATUS_SUCCESS;
            $bIsSuccessful = $b->getStatus() === Publication::STATUS_SUCCESS;

            // Prefer editable publications (failed/pending) over successful history.
            if ($aIsSuccessful !== $bIsSuccessful) {
                return $aIsSuccessful <=> $bIsSuccessful;
            }

            return $this->publicationTimestamp($b) <=> $this->publicationTimestamp($a);
        });

        $byChannel = [];
        foreach ($publications as $publication) {
            $channelId = $publication->getChannel()->getId();
            if (!\is_string($channelId) || $channelId === '') {
                continue;
            }

            if (!\array_key_exists($channelId, $byChannel)) {
                $byChannel[$channelId] = $publication;
            }
        }

        return $byChannel;
    }

    private function publicationTimestamp(Publication $publication): int
    {
        return $publication->getTime()->getStartDate()?->getTimestamp() ?? 0;
    }

    public function forContentOnChannel(Content $content, ChannelInterface $channel): array
    {
        $contentId = $content->getId();
        if (!\is_string($contentId) || '' === $contentId) {
            return [];
        }

        return $this->findBy(['content' => $content, 'channel' => $channel]);
    }

    public function getAvailable(Content $content, ChannelInterface $channel): iterable
    {
        return $this->createQueryBuilder()
            ->setRewindable(false)
            ->field('content')->equals($content)
            ->field('channel')->equals($channel)
            ->field('status')->in([Publication::STATUS_FAILED, ''])
            ->getQuery()
            ->getIterator();
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
