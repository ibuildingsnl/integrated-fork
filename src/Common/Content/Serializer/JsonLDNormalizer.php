<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content\Serializer;

use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Normalizer\NormalizerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class JsonLDNormalizer implements \Symfony\Component\Serializer\Normalizer\NormalizerInterface
{
    /**
     * @var string
     */
    public const FORMAT = 'json-ld';

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param ContentInterface $object
     */
    public function normalize($object, $format = null, array $context = []): array|\ArrayObject|bool|float|int|string|null
    {
        if ($normalized = $this->normalizer->normalize($object, $context)) {
            return $normalized + ['@context' => 'http://schema.org'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return self::FORMAT === $format && $data instanceof ContentInterface;
    }

    /**
     * @return array<string, bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        // Support is decided per object, so the result must not be cached.
        return ['*' => false];
    }
}
