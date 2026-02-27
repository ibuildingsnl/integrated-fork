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
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Common\Content\ContentInterface;

class BulkCapabilityResolver
{
    private DocumentManager $documentManager;

    /** @var array<string, ContentType|null> */
    private array $cache = [];

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @param array<int, mixed> $content
     */
    public function supports(array $content, string $feature): bool
    {
        foreach ($content as $item) {
            if (!$item instanceof ContentInterface) {
                return false;
            }

            $contentType = $this->getContentType($item->getContentType());
            $supported = false;

            switch ($feature) {
                case 'workflow':
                    $supported = null !== $contentType && $contentType->hasOption('workflow');

                    break;

                case 'premium':
                    $supported = null !== $contentType
                        && $contentType->hasField('premium')
                        && method_exists($item, 'setPremium');

                    break;

                case 'featured':
                    $supported = null !== $contentType
                        && $contentType->hasField('featured')
                        && method_exists($item, 'setFeatured');

                    break;

                case 'publishTime':
                    $supported = method_exists($item, 'getPublishTime')
                        && method_exists($item, 'setPublishTime');

                    break;

                case 'seoMeta':
                    $supported = null !== $contentType
                        && $contentType->hasField('seoMetadata')
                        && method_exists($item, 'setSeoMetadata')
                        && method_exists($item, 'getSeoMetadata');

                    break;

                case 'canonical':
                    $supported = null !== $contentType
                        && $contentType->hasField('source')
                        && $contentType->hasField('sourceUrl')
                        && method_exists($item, 'setSource')
                        && method_exists($item, 'setSourceUrl');

                    break;

                case 'authors':
                    $supported = null !== $contentType
                        && $contentType->hasField('authors')
                        && method_exists($item, 'setAuthors');

                    break;

                default:
                    return false;
            }

            if (!$supported) {
                return false;
            }
        }

        return true;
    }

    private function getContentType(?string $contentType): ?ContentType
    {
        if (!isset($this->cache[$contentType])) {
            $this->cache[$contentType] = $this->documentManager->getRepository(ContentType::class)->find($contentType);
        }

        return $this->cache[$contentType];
    }
}
