<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class PublicationRepository extends DocumentRepository
{
    /** @return Publication[] */
    public function forContent(Content|string $content): array
    {
        return []; // @todo
    }

    public function add(Publication $publication): void
    {
        $this->getDocumentManager()->persist($publication);
    }
}
