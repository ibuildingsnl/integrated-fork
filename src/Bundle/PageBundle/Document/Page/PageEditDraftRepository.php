<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Document\Page;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class PageEditDraftRepository extends DocumentRepository
{
    public function findOneByPageAndUser(string $pageId, string $userId): ?PageEditDraft
    {
        /** @var PageEditDraft|null $draft */
        $draft = $this->findOneBy([
            'pageId' => $pageId,
            'userId' => $userId,
        ]);

        return $draft;
    }

    public function add(PageEditDraft $draft): void
    {
        $this->getDocumentManager()->persist($draft);
    }

    public function remove(PageEditDraft $draft): void
    {
        $this->getDocumentManager()->remove($draft);
    }
}
