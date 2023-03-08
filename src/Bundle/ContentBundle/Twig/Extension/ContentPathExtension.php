<?php

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Common\Content\ContentInterface;
use Solarium\QueryType\Select\Result\Document;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentPathExtension extends AbstractExtension
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('integrated_content_path', [$this, 'getContentPath']),
        ];
    }

    /**
     * @param Document|ContentInterface $data
     *
     * @return array|null
     */
    public function getContentPath($data)
    {
        if ($data instanceof Document && isset($data['type_id'])) {
            $data = $this->documentManager->getRepository(Content::class)->find($data['type_id']);
        }

        if (!$data instanceof ContentInterface) {
            return false;
        }

        $path = [];
        while ($relationReferences = $data->getReferenceByRelationType('parent')) {
            if (isset($path[$data->getId()])) {
                // circular reference
                break;
            }
            $path[$data->getId()] = (string)$relationReferences;
        }

        if ($data instanceof Taxonomy) {
            /** @var Taxonomy $data */
            if ($taxonomyParent = $data->getParentID()) {
                /** @var Taxonomy $parent */
                $parent = $this->documentManager->getRepository(Content::class)->find($taxonomyParent);
                $parentParent = $this->documentManager->getRepository(Content::class)->find($parent);
                if ($parent) {
                    $path[$parent->getId()] = $parent->getTitle();
                }
                if ($parentParent) {
                    $path[$parentParent->getId()] = $parentParent->getTitle();
                }
            }
        }

        return array_reverse($path, true);
    }
}
