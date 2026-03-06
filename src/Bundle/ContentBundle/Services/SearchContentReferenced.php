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
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type as MongoType;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;

/**
 * Class SearchContentReferenced.
 */
class SearchContentReferenced
{
    /**
     * @const IGNORE_CLASSES
     */
    public const IGNORE_CLASSES = ['Integrated\Bundle\ContentBundle\Document\Bulk\BulkAction'];

    private DocumentManager $dm;

    /**
     * SearchContentReferenced constructor.
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @throws \Exception
     */
    public function getReferenced(mixed $document): array
    {
        return $this->prepareReferenced($this->findReferencedDocuments($document));
    }

    /**
     * @return array<int, object>
     *
     * @throws \Exception
     */
    public function getReferencedDocuments(mixed $document): array
    {
        return $this->findReferencedDocuments($document);
    }

    /**
     * @return array{className: class-string, metadata: ClassMetadata<object>, idField: string, idValue: mixed}
     *
     * @throws \Exception
     */
    public function getDeletedInfo(mixed $document, DocumentManager $documentManager): array
    {
        if (!\is_object($document)) {
            throw new \InvalidArgumentException('Expected an object document');
        }

        $className = $document::class;
        $deleted = [
            'className' => $className,
            'metadata' => $documentManager->getClassMetadata($className),
        ];

        $idField = current($deleted['metadata']->getIdentifier());
        if ($idField === false) {
            throw new \Exception('Unable to resolve identifier field for deleted object');
        }

        $deleted['idField'] = $idField;
        $deleted['idValue'] = $deleted['metadata']->getFieldValue($document, $deleted['idField']);

        if (MongoType::hasType($deleted['metadata']->getTypeOfField($deleted['idField']))) {
            $typeClass = MongoType::getType($deleted['metadata']->getTypeOfField($deleted['idField']));
            $deleted['idValue'] = $typeClass->convertToDatabaseValue($deleted['idValue']);
        } else {
            throw new \Exception('The identifer of the deleted object must have a valid Doctrine field type');
        }

        return $deleted;
    }

    /**
     * @return array<int, object>
     *
     * @throws \Exception
     */
    private function findReferencedDocuments(mixed $document): array
    {
        $metadataFactory = $this->dm->getMetadataFactory();
        $deleted = $this->getDeletedInfo($document, $this->dm);
        $allMetadata = $metadataFactory->getAllMetadata();

        $referenced = [];

        /** @var ClassMetadata $classMetadata */
        foreach ($allMetadata as $classMetadata) {
            if (\in_array($classMetadata->getName(), $this::IGNORE_CLASSES)) {
                continue;
            }

            if ($classMetadata->isMappedSuperclass || $classMetadata->isEmbeddedDocument) {
                continue;
            }

            $associations = $classMetadata->getAssociationNames();
            foreach ($associations as $assocFieldName) {
                $assocClassName = $classMetadata->getAssociationTargetClass($assocFieldName);

                if (!\is_string($assocClassName)) {
                    continue; // Skip empty class
                }

                if ($deleted['className'] == $assocClassName || is_subclass_of($deleted['className'], $assocClassName)) {
                    $items = $this->dm->createQueryBuilder($classMetadata->getName())
                        ->field($assocFieldName.'.$id')
                        ->equals($deleted['idValue'])
                        ->getQuery()
                        ->toArray();

                    if ($items) {
                        foreach ($items as $item) {
                            $referenced[] = $item;
                        }
                    }
                } elseif ($fieldMetaData = $metadataFactory->getMetadataFor($assocClassName)) {
                    $fieldAssociations = $fieldMetaData->getAssociationNames();

                    if (!$fieldMetaData->isEmbeddedDocument) {
                        continue;
                    }

                    foreach ($fieldAssociations as $fieldAssociation) {
                        $fieldAssocClassName = $fieldMetaData->getAssociationTargetClass($fieldAssociation);
                        $allow = $deleted['className'] == $fieldAssocClassName || is_subclass_of($deleted['className'], $fieldAssocClassName);

                        if ($allow) {
                            $items = $this->dm->createQueryBuilder($classMetadata->getName())
                                ->field($assocFieldName.'.'.$fieldAssociation.'.$id')
                                ->equals($deleted['idValue'])
                                ->getQuery()
                                ->toArray();

                            if ($items) {
                                foreach ($items as $item) {
                                    $referenced[] = $item;
                                }
                            }
                        }
                    }
                }
            }
        }

        return array_values(array_filter($referenced, function ($item): bool {
            return !($item instanceof Publication);
        }));
    }

    /**
     * @param array<int, object> $referenced
     *
     * @return array<string, array<string, mixed>>
     */
    private function prepareReferenced(array $referenced): array
    {
        $output = [];
        foreach ($referenced as $item) {
            if (!\is_callable([$item, 'getId'])) {
                continue;
            }

            $id = (string) \call_user_func([$item, 'getId']);
            $key = $item::class.'-'.$id;
            if ($item instanceof Content) {
                $output[$key] = [
                    'action' => 'integrated_content_content_edit',
                    'id' => $id,
                    'name' => method_exists($item, 'getTitle') ? $item->getTitle() : $item::class,
                ];
            } else {
                $output[$key] = [
                    'id' => $id,
                    'name' => $item::class,
                ];
            }
        }

        return $output;
    }
}
