<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Bulk;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Common\Content\ContentInterface;

class BulkCapabilityResolver
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function supports(array $content, string $feature): bool
    {
        $result = true;

        foreach ($content as $item) {
            if (!$item instanceof ContentInterface) {
                return false;
            }

            if ($result === false) {
                return false;
            }

            $contentType = $this->getContentType($item->getContentType());

            switch ($feature) {
                case 'workflow':
                    $result = $result && null !== $contentType && $contentType->hasOption('workflow');

                    break;

                case 'premium':
                    $result = $result
                        && null !== $contentType
                        && $contentType->hasField('premium')
                        && \method_exists($item, 'setPremium');

                    break;

                case 'featured':
                    $result = $result
                        && null !== $contentType
                        && $contentType->hasField('featured')
                        && \method_exists($item, 'setFeatured');

                    break;

                case 'publishTime':
                    $result = $result
                        && \method_exists($item, 'getPublishTime')
                        && \method_exists($item, 'setPublishTime');

                    break;

                case 'seoMeta':
                    $result = $result
                        && null !== $contentType
                        && $contentType->hasField('seoMetadata')
                        && \method_exists($item, 'setSeoMetadata')
                        && \method_exists($item, 'getSeoMetadata');

                    break;

                case 'canonical':
                    $result = $result
                        && null !== $contentType
                        && $contentType->hasField('source')
                        && $contentType->hasField('sourceUrl')
                        && \method_exists($item, 'setSource')
                        && \method_exists($item, 'setSourceUrl');

                    break;

                case 'authors':
                    $result = $result
                        && null !== $contentType
                        && $contentType->hasField('authors')
                        && \method_exists($item, 'setAuthors');

                    break;

                default:
                    return false;
            }
        }

        return $result;
    }

    private function getContentType(?string $contentType): ?ContentType
    {
        if (!isset($this->cache[$contentType])) {
            $this->cache[$contentType] = $this->documentManager->getRepository(ContentType::class)->find($contentType);
        }

        return $this->cache[$contentType];
    }
}
