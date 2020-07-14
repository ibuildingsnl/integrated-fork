<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\InstallerBundle\Serializer\Normalizer;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DocumentNormalizer implements DenormalizerInterface
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var ObjectNormalizer
     */
    private $objectNormalizer;

    const SUPPORTED_DOCUMENTS = [
        ContentType::class,
    ];

    /**
     * @param DocumentManager  $documentManager
     * @param ObjectNormalizer $objectNormalizer
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->objectNormalizer = new ObjectNormalizer();
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $conversion = [];
        if ($this->supports($class)) {
            $meta = $this->documentManager->getClassMetadata($class);

            foreach ($data as $key => $value) {
                if (!$meta->hasField($key)) {
                    continue;
                }

                $fieldMapping = $meta->getFieldMapping($key);
                if (!isset($fieldMapping['targetDocument'])) {
                    continue;
                }

                if (!\in_array($fieldMapping['targetDocument'], $this::SUPPORTED_DOCUMENTS)) {
                    continue;
                }

                $conversion[$key] = [
                    'class' => $fieldMapping['targetDocument'],
                    'ids' => \is_array($data[$key]) ? $data[$key] : [$data[$key]],
                ];

                unset($data[$key]);
            }
        }

        $document = $this->objectNormalizer->denormalize($data, $class, $format, $context);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($conversion as $field => $data) {
            $items = [];

            foreach ($data['ids'] as $id) {
                $items[] = $this->documentManager->getRepository($data['class'])->find($id);
            }

            $propertyAccessor->setValue($document, $field, $items);
        }

        return $document;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if (!\is_array($data)) {
            return false;
        }

        return $this->supports($type);
    }

    /**
     * Check if the class is a mongodb document class registered by the
     * registered document manager.
     *
     * @param $class
     *
     * @return bool
     */
    protected function supports($class)
    {
        $meta = $this->documentManager->getClassMetadata($class);

        if ($meta->isMappedSuperclass || $meta->isEmbeddedDocument) {
            return false;
        }

        $identifier = $meta->getIdentifierFieldNames();

        if (empty($identifier)) {
            return false;
        }

        return true;
    }
}
