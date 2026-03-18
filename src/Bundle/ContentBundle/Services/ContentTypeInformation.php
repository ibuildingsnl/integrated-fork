<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;

class ContentTypeInformation
{
    /**
     * @var DocumentManager
     */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @return array
     */
    public function getPublishingAllowedContentTypes(string $channelId)
    {
        $result = [];

        $contentTypes = $this->dm->getRepository(ContentType::class)->findAll();
        foreach ($contentTypes as $contentType) {
            if (!$this->isPublishingAllowedForChannel($contentType, $channelId)) {
                continue;
            }

            $result[] = $contentType->getId();
        }

        return $result;
    }

    /**
     * @param list<string> $defaultExcludedTypes
     *
     * @return list<string>
     */
    public function getSitemapAllowedContentTypes(string $channelId, array $defaultExcludedTypes = []): array
    {
        $result = [];
        $excluded = array_flip(array_map(static fn (string $type): string => strtolower($type), $defaultExcludedTypes));

        $contentTypes = $this->dm->getRepository(ContentType::class)->findAll();
        foreach ($contentTypes as $contentType) {
            if (!$this->isPublishingAllowedForChannel($contentType, $channelId)) {
                continue;
            }

            $contentTypeId = (string) $contentType->getId();
            if ($contentTypeId === '') {
                continue;
            }

            $sitemapSetting = strtolower(trim((string) $contentType->getOption('sitemap')));
            if ($sitemapSetting === 'disabled') {
                continue;
            }

            if ($sitemapSetting !== 'enabled' && isset($excluded[strtolower($contentTypeId)])) {
                continue;
            }

            $result[] = $contentTypeId;
        }

        return $result;
    }

    private function isPublishingAllowedForChannel(ContentType $contentType, string $channelId): bool
    {
        $channelOption = $contentType->getOption('channels');
        if ((isset($channelOption['disabled']) && (int) $channelOption['disabled'] === 2)
            || $contentType->getOption('publication') === 'disabled') {
            return false;
        }

        if (isset($channelOption['restricted']) && (\count($channelOption['restricted']) > 0) && !\in_array($channelId, $channelOption['restricted'], true)) {
            return false;
        }

        return true;
    }
}
