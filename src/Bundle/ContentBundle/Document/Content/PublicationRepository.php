<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Common\Channel\ChannelInterface;

class PublicationRepository extends DocumentRepository implements PublicationRepositoryInterface
{
    public function forContentByChannel(Content $content): array
    {
        if (!$content->getId()) {
            return [];
        }
        $publications = $this->findBy(['content' => $content]);
        return array_combine(
            array_map(fn(Publication $p) => $p->getChannel()->getId(), $publications),
            $publications,
        );
    }

    public function forContentOnChannel(Content $content, ChannelInterface $channel): array
    {
        return $this->findBy(['content' => $content, 'channel' => $channel]);
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
