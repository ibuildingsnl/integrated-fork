<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * @extends DocumentRepository<ContentEditDraft>
 */
class ContentEditDraftRepository extends DocumentRepository
{
    public function findOneByContentAndUser(string $contentId, string $userId): ?ContentEditDraft
    {
        /** @var ContentEditDraft|null $draft */
        $draft = $this->findOneBy([
            'contentId' => $contentId,
            'userId' => $userId,
        ]);

        return $draft;
    }

    public function add(ContentEditDraft $draft): void
    {
        $this->getDocumentManager()->persist($draft);
    }

    public function remove(ContentEditDraft $draft): void
    {
        $this->getDocumentManager()->remove($draft);
    }
}
