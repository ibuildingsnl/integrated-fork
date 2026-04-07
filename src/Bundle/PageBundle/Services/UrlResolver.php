<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\ContentInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class UrlResolver
{
    /**
     * @var ContentTypeControllerManager
     */
    protected $controllerManager;

    /**
     * @var ChannelContextInterface
     */
    protected $channelContext;

    /**
     * @var array<string, array<string, ContentTypePage|null>>
     */
    protected array $contentTypePages = [];

    /**
     * @var array<string, string|null>
     */
    private array $generatedUrls = [];

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(
        ContentTypeControllerManager $controllerManager,
        ChannelContextInterface $channelContext,
        RouterInterface $router,
        DocumentManager $dm,
    ) {
        $this->controllerManager = $controllerManager;
        $this->channelContext = $channelContext;
        $this->router = $router;
        $this->dm = $dm;
    }

    /**
     * Returns the correct path for symfony routing module (replace "#[string]#" with "{[string}").
     *
     * @return string
     */
    public function getRoutePath(ContentTypePage $page)
    {
        return preg_replace_callback(
            '/(#)([\s\S]+?)(#)/',
            function ($matches) {
                return \sprintf('{%s}', $matches[2]);
            },
            $page->getPath()
        );
    }

    /**
     * @return string
     */
    public function getRouteName(ContentTypePage $page)
    {
        return \sprintf('%s_%s', ContentTypePageLoader::ROUTE_PREFIX, $page->getId());
    }

    public function generateUrl(ContentInterface $document, ?string $channelId = null, bool $fallback = true): ?string
    {
        $channelId = $this->resolveChannelId($channelId);
        $cacheKey = $this->getGeneratedUrlCacheKey($document, $channelId, $fallback);
        $contentTypeId = (string) $document->getContentType();

        if (\array_key_exists($cacheKey, $this->generatedUrls)) {
            return $this->generatedUrls[$cacheKey];
        }

        $page = $this->getContentTypePageById($contentTypeId, $channelId);

        if ($page instanceof ContentTypePage) {
            return $this->generatedUrls[$cacheKey] = $this->getContentTypePageUrl($page, $document);
        }

        if (!$fallback) {
            return $this->generatedUrls[$cacheKey] = null;
        }

        // fallback /app_*.php/content/contentType/slug, in production /content/contentType/slug
        return $this->generatedUrls[$cacheKey] = \sprintf(
            '%s/content/%s/%s',
            $this->router->getContext()->getBaseUrl(),
            $contentTypeId,
            // todo INTEGRATED-440 add Slug to ContentInterface
            $document->getSlug()
        );
    }

    /**
     * todo INTEGRATED-440 add Slug and getReferenceByRelationIdto ContentInterface.
     *
     * @return string
     */
    public function getContentTypePageUrl(ContentTypePage $page, ContentInterface $document)
    {
        return $this->router->generate(
            $this->getRouteName($page),
            $this->getRoutingParamaters($page, $document)
        );
    }

    /**
     * @return array
     */
    protected function getRoutingParamaters(ContentTypePage $page, ContentInterface $content)
    {
        $parameters = ['slug' => $content->getSlug()];

        foreach ($this->getRelationIds($page) as $relationId) {
            if ($relation = $content->getReferenceByRelationId($relationId)) {
                // keep track of last document
                // if there is a previous relation then the new reference should be searched that relation
                // first time use current document
                $content = $relation;

                $parameters[$relationId] = $relation->getSlug();
            } else {
                // no relation found, as fallback use relationId
                $parameters[$relationId] = $relationId;
            }
        }

        return $parameters;
    }

    /**
     * @return array
     */
    protected function getRelationIds(ContentTypePage $page)
    {
        $relationIds = [];

        if (preg_match_all('/#([\w]+?)#/', $page->getPath(), $matches)) {
            foreach ($matches[1] as $match) {
                $relationIds[] = $match;
            }
        }

        return $relationIds;
    }

    protected function getContentTypePageById(string $contentTypeId, ?string $channelId = null): ?ContentTypePage
    {
        $resolvedChannelId = $this->resolveChannelId($channelId);
        $channelCacheKey = $resolvedChannelId ?? '_null';
        $channelPages = $this->contentTypePages[$channelCacheKey] ?? [];

        if (\array_key_exists($contentTypeId, $channelPages)) {
            return $channelPages[$contentTypeId];
        }

        $page = $this->dm->getRepository(ContentTypePage::class)
            ->findOneBy([
                'channel.$id' => $resolvedChannelId,
                'contentType.$id' => $contentTypeId,
            ]);

        $this->contentTypePages[$channelCacheKey][$contentTypeId] = $page instanceof ContentTypePage ? $page : null;

        return $this->contentTypePages[$channelCacheKey][$contentTypeId];
    }

    private function resolveChannelId(?string $channelId = null): ?string
    {
        if (null !== $channelId) {
            return (string) $channelId;
        }

        $channel = $this->channelContext->getChannel();

        if ($channel instanceof Channel) {
            return $channel->getId();
        }

        return null;
    }

    private function getGeneratedUrlCacheKey(ContentInterface $document, ?string $channelId, bool $fallback): string
    {
        return implode(':', [
            $channelId ?? '_null',
            (string) $document->getContentType(),
            (string) ($document->getId() ?? $document->getSlug()),
            $fallback ? '1' : '0',
        ]);
    }
}
