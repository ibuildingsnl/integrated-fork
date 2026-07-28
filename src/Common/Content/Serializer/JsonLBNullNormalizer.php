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

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class JsonLBNullNormalizer implements NormalizerInterface
{
    /**
     * @var string
     */
    public const FORMAT = 'json-ld';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array|\ArrayObject|bool|float|int|string|null
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return self::FORMAT === $format;
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
