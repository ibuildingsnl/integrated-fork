<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\MongoDB\Serializer\Normalizer;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class DocumentNormalizer implements NormalizerInterface, DenormalizerInterface
{
    use SerializerAwareTrait;

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->dm;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        try {
            $document = $this->getDocumentManager()->getRepository($class)->find($data);
        } catch (\Exception $e) {
            return null;
        }

        return $document;
    }

    public function normalize($object, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $meta = $this->getDocumentManager()->getClassMetadata($object::class);

        $keys = [];

        foreach ($meta->getIdentifierFieldNames() as $field) {
            $keys[$field] = $meta->getFieldValue($object, $field);
        }

        return $keys;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        if (!\is_array($data)) {
            return false;
        }

        return $this->supports($type);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        if (!\is_object($data)) {
            return false;
        }

        return $this->supports($data::class);
    }

    /**
     * Check if the class is a mongodb document class registered by the
     * registered document manager.
     *
     * @param class-string $class
     */
    protected function supports(string $class): bool
    {
        $factory = $this->getDocumentManager()->getMetadataFactory();

        if ($factory->hasMetadataFor($class)) {
            return true;
        }

        if (!$factory->isTransient($class)) {
            return true;
        }

        return false;
    }
}
