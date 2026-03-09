<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Solr\Extension;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;

class SeoMetaExtension implements TypeExtensionInterface
{
    private const SCORE_FEEDBACK = 'feedback';
    private const VALID_SCORES = ['good', 'ok', 'bad', self::SCORE_FEEDBACK];

    public function __construct(
        private readonly ResolverInterface $resolver,
    ) {
    }

    public function build(ContainerInterface $container, $data, array $options = []): void
    {
        if (!$data instanceof ContentInterface) {
            return;
        }

        $container->set('has_seo_metadata', false);

        $contentType = $this->resolver->getType($data->getContentType());
        if (!$contentType || !$contentType->hasField('seoMetadata')) {
            return;
        }

        $container->set('has_seo_metadata', true);

        if (!method_exists($data, 'getSeoMetadata')) {
            $this->setDefaultScores($container);

            return;
        }

        $seoMeta = $data->getSeoMetadata();
        if (!$seoMeta instanceof SeoMeta) {
            $this->setDefaultScores($container);

            return;
        }

        $container->set('seo_score', $this->normalizeScore($seoMeta->getSeoScore()));
        $container->set('readability_score', $this->normalizeScore($seoMeta->getReadabilityScore()));
    }

    public function getName(): string
    {
        return 'integrated.content';
    }

    private function setDefaultScores(ContainerInterface $container): void
    {
        $container->set('seo_score', self::SCORE_FEEDBACK);
        $container->set('readability_score', self::SCORE_FEEDBACK);
    }

    private function normalizeScore(?string $score): string
    {
        $score = strtolower(trim((string) $score));

        if (\in_array($score, self::VALID_SCORES, true)) {
            return $score;
        }

        return self::SCORE_FEEDBACK;
    }
}
