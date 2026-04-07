<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Twig\Extension;

use Integrated\Bundle\PageBundle\Services\SolrUrlExtractor;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Common\Content\ContentInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class UrlExtension extends AbstractExtension
{
    /**
     * @var UrlResolver
     */
    protected $urlResolver;

    /**
     * @var SolrUrlExtractor
     */
    protected $solrUrlExtractor;

    public function __construct(UrlResolver $urlResolver, SolrUrlExtractor $solrUrlExtractor)
    {
        $this->urlResolver = $urlResolver;
        $this->solrUrlExtractor = $solrUrlExtractor;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('integrated_url', $this->getUrl(...)),
        ];
    }

    /**
     * @param null $channelId
     * @param bool $fallback
     *
     * @return string|null
     */
    public function getUrl($document, $channelId = null, $fallback = true)
    {
        try {
            if ($document instanceof ContentInterface) {
                return $this->urlResolver->generateUrl($document, $channelId, $fallback);
            }

            // probably solr document
            return $this->solrUrlExtractor->getUrl($document, $channelId);
        } catch (RouteNotFoundException $e) {
            // Missing content-type page routes should not break rendering contexts
            // like admin content edit toolbars.
            return null;
        }
    }

    public function getName(): string
    {
        return 'integrated_page_url';
    }
}
