<?php

namespace Integrated\Bundle\ImportBundle\Import\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Connector;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ImportBundle\Import\Create\Create;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;
use Integrated\Common\Channel\Connector\Config\Config;

class BaseConverter
{
    public static function fieldMapper($fieldMapping, $row, $data, $col)
    {
        $newData = [];

        foreach ($row as $value) {
            if (isset($fieldMapping[$data[0][$col]])) {
                $mappedField = $fieldMapping[$data[0][$col]];

                if ($mappedField) {
                    if (strpos($mappedField, 'field-') === 0) {
                        $mappedField = str_replace('field-', '', $mappedField);
                        $mappedFieldParts = explode('.', $mappedField);
                        if (\count($mappedFieldParts) == 2) {
                            $newData[$mappedFieldParts[0]][$mappedFieldParts[1]] = $value;
                        } else {
                            $newData[$mappedField] = $value;
                        }
                    }
                }
            }
            ++$col;
        }

        return $newData;
    }

    public static function formatDateTime($dateTime, $offset = '+2:00')
    {
        return str_replace(' ', 'T', $dateTime) . $offset;
    }

    public static function formatDateString($dateString)
    {
        if (\strlen($dateString) == 10) {
            $date = new \DateTime($dateString);
            return $date->format(\DateTime::ISO8601);  // 'T00:00:00' can be added if needed
        }
        return $dateString;  // return original string if it doesn't meet the condition
    }

    public static function processDateFields(&$newData)
    {
        $dateFields = [
            'created_at' => 'formatDateTime',
            'updated_at' => 'formatDateTime',
            'start_date' => 'formatDateString',
            'end_date' => 'formatDateString'
        ];

        foreach ($dateFields as $field => $method) {
            if (isset($newData[$field])) {
                $newData[$field] = self::$method($newData[$field]);
            }
        }
    }

    public static function setPublished($newData, $newObject)
    {

        if (array_key_exists('published', $newData)) {
            if ($newData['published'] === true || $newData['published'] === 'published' || $newData['published'] === 'publish' || $newData['published'] === 1) {
                $newObject->isPublished(true);
            }
        }

    }

    public static function setPublicationDate($row, $newData, $newObject)
    {
        $startDate = self::getDateFromData($row, 'publiceren_van', $newData, 'created_at', $newObject->getCreatedAt());
        $endDate = self::getDateFromData($row, 'publiceren_tot', $newData, 'end_date');

        $newObject->getPublishTime()->setStartDate($startDate);
        if ($endDate) {
            $newObject->getPublishTime()->setEndDate($endDate);
        }
    }

    private static function getDateFromData($rowData, $rowKey, $newData, $newDataKey, $default = null)
    {
        if (isset($newData[$newDataKey]) && $newData[$newDataKey] !== '') {
            return new \DateTime($newData[$newDataKey]);
        }

        if (isset($rowData[$rowKey]) && $rowData[$rowKey] !== '') {
            return new \DateTime($rowData[$rowKey]);
        }

        return $default;
    }

    public static function processPersonObject($newObject, $row, $importDefinition, $storageManager)
    {
        // Process name fields
        if (strpos($newObject->getLastName(), ' ') !== false
            && (empty($newObject->getFirstName())
                || strpos($newObject->getLastName(), $newObject->getFirstName()) !== false)
        ) {
            list($firstName, $lastName) = explode(' ', $newObject->getLastName(), 2);
            $newObject->setFirstName($firstName);
            $newObject->setLastName($lastName);
        }

        // Process picture
        if (!empty($row['picture_src'])) {
            $path = false;
            foreach ($importDefinition->getChannels() as $channel) {
                $path = '/home/testpi-integrated/importfiles/' . $channel->getId(
                    ) . '/images/auteurfotos/' . $row['picture_src'];
            }
            //TODO: this seems highly specific and not usable in general
            if ($path !== false && file_exists($path)) {
                $storage = $storageManager->write(
                    new MemoryReader(
                        file_get_contents($path),
                        new StorageMetadata(
                            pathinfo($path, \PATHINFO_EXTENSION),
                            mime_content_type($path),
                            new ArrayCollection(),
                            new ArrayCollection()
                        )
                    )
                );
                $newObject->setPicture($storage);
            }
        }
        return $newObject;  // Return the modified object
    }

    public static function processImageElements($html, $newObject, $importDefinition, $documentManager, $storageManager)
    {
        $tags = ['a', 'img'];
        //TODO: Check functioning
        foreach ($tags as $tag) {
            foreach ($html->find($tag) as $element) {
                if (!$importDefinition->getImageContentType() || !$importDefinition->getImageRelation()) {
                    continue;
                }

                $href = ($tag == 'a') ? $element->href : $element->src;
                $title = ($tag == 'a') ? false : $element->title;

                if (strpos($href, '/') === 0) {
                    if (!$importDefinition->getImageBaseUrl()) {
                        continue;
                    }
                    $href = rtrim($importDefinition->getImageBaseUrl(), '/') . $href;
                }

                if (stripos($href, '.png') === false
                    && stripos($href, '.jpg') === false
                    && stripos($href, '.jpeg') === false
                    && stripos($href, '.gif') === false
                    && stripos($href, '.pdf') === false) {
                    continue;
                }

                if (!$title && $tag == 'a') {
                    foreach ($element->find('img') as $img) {
                        $title = $img->title;
                        if (!$title) {
                            $title = basename($img->src);
                            $title = str_replace(['.png', '.jpg', '.jpeg', '.gif'], '', $title);
                        }
                    }
                }

                if (!$title) {
                    $title = basename($href);
                    $title = str_replace('.' . pathinfo($href, \PATHINFO_EXTENSION), '', $title);
                }

                if ($href) {
                    $image = Create::createFileFromUrl($href, $newObject, $importDefinition, $storageManager, $documentManager, $title);

                    if ($image === false) {
                        continue;
                    }

                    $relation = new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation();
                    $relationType = $tag == 'img' ? 'embedded' : $importDefinition->getImageRelation()->getType();
                    $relationId = $tag == 'img' ? '__editor_image' : $importDefinition->getImageRelation()->getId();

                    $skipImage = $tag == 'img' && $newObject->getReferencesByRelationType('embedded') &&
                                 array_search($image->getId(), array_column($newObject->getReferencesByRelationType('embedded'), 'id')) !== false;


                    $relation->setRelationType($relationType);
                    $relation->setRelationId($relationId);
                    $relation->addReference($image);
                    $newObject->addRelation($relation);
                }

                // Updating the tag attributes based on the tag type
                if ($tag == 'a') {
                    $element->href = '/storage/' . $image->getId() . '.' . pathinfo($href, \PATHINFO_EXTENSION);
                } elseif ($tag == 'img') {
                    $element->outertext = '
                    <img src="/storage/' . $image->getId() . '.jpg"
                    class="img-responsive"
                    title="' . htmlspecialchars($title) . '"
                    alt="' . htmlspecialchars($title) . '"
                    data-integrated-id="' . $image->getId() . '" />';
                }
            }
        }
        return $html;
    }


    public static function processMetadata($row, $newObject, $importDefinition)
    {
        if (isset($row['wp:post_id'])) {
            $newObject->getMetadata()->set('wpPostId', $row['wp:post_id']);
            $newObject->getMetadata()->set(
                'wpUrl',
                (isset($row['wp:attachment_url'])) ? $row['wp:attachment_url'] : $row['link']
            );
            $newObject->getMetadata()->set('importDate', date('Ymd'));
            $newObject->getMetadata()->set('importImageBaseUrl', $importDefinition->getImageBaseUrl());
        }

        if (isset($row['contentitem_id'])) {
            $newObject->getMetadata()->set('externalId', $row['contentitem_id']);
            $newObject->getMetadata()->set('importDate', date('Ymd'));
            $newObject->getMetadata()->set('importImageBaseUrl', $importDefinition->getImageBaseUrl());
        }

        if (isset($row['meta_yoast_wpseo_canonical']) && $newObject instanceof Article) {
            $newObject->setSourceUrl($row['meta_yoast_wpseo_canonical']);
        }

        // premium articles
        if ($newObject->getMetadata()->get('premium') == 1) {
            $newObject->isPremium(true);
        }

        return $newObject;
    }

    public static function checkForExistingContent($field, $dbField, $value, $importDefinition, $documentManager)
    {
        $doubleArticle = $documentManager
            ->getRepository(Content::class)
            ->findOneBy(
                [
                    "metadata.data.{$dbField}" => $value,
                    'metadata.data.importImageBaseUrl' => $importDefinition->getImageBaseUrl(),
                ]
            );

        return $doubleArticle;
    }

    public static function processAuthorField($mappedField, $value, $newObject, $importDefinition, $documentManager)
    {
        /* @var $newObject Article */
        $newObject->getAuthors()->clear();
        if (trim($value) != '') {
            foreach (explode(',', $value) as $auteur) {
                if (trim($auteur) != '') {
                    $author = new Author();
                    $author->setPerson(
                        Create::addAuthor(
                            $auteur,
                            $importDefinition->getImageBaseUrl(),
                            $importDefinition->getAuthorContentType(),
                            $documentManager
                        )
                    );
                    $newObject->addAuthor($author);
                }
            }
        }
    }

    public static function processRelationField($mappedField, $value, $newObject, $importDefinition, $documentManager, $storageManager)
    {
        $relationId = str_replace('relation-', '', $mappedField);
        if ($relation = $newObject->getRelation($relationId)) {
            $newObject->removeRelation($relation);
        }

        $relation = $documentManager->getRepository(Relation::class)->find($relationId);

        $targets = $relation->getTargets();
        $targetContentType = $targets[0];

        // TODO: allow choose content type
        /*$targetContentType = $this->documentManager->find(
            ContentType::class,
            $target
        );*/

        if ($value) {
            $relation2 = new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation();
            $relation2->setRelationId($relation->getId());
            $relation2->setRelationType($relation->getType());

            if (!\is_array($value)) {
                $value = explode(',', $value);
            }

            foreach ($value as $valueName) {
                $link = false;
                $valueName = trim($valueName);
                if ($targetContentType->getClass() == Taxonomy::class || $targetContentType->getClass() == Article::class) {
                    $link = $documentManager->getRepository(Content::class)->findOneBy(
                        ['title' => $valueName, 'contentType' => $targetContentType->getId()]
                    );
                }

                if (!$link) {
                    $link = $targetContentType->create();
                    if (strpos($valueName, 'http') !== false) {
                        $link->setTitle(basename($valueName));
                    } else {
                        $link->setTitle($valueName);
                    }
                    $link->getMetadata()->set('importDate', date('Ymd'));
                    $link->getMetadata()->set('externalId', $valueName);
                    $link->getMetadata()->set('importImageBaseUrl', $importDefinition->getImageBaseUrl());

                    foreach ($importDefinition->getChannels() as $channel) {
                        $link->addChannel($channel);
                    }

                    $documentManager->persist($link);
                    $documentManager->flush();
                }

                if ($link instanceof Image || $link instanceof File) {
                    $path = false;
                    $path = $valueName;

                    if (strpos($path, 'http') === 0) {
                        $tmpfile = tempnam('/tmp/', 'file') . '.' . pathinfo(
                                $path,
                                \PATHINFO_EXTENSION
                            );
                        file_put_contents($tmpfile, @file_get_contents($path));
                        $path = $tmpfile;
                    }

                    if ($path !== false && file_exists($path)) {
                        $storage = $storageManager->write(
                            new MemoryReader(
                                file_get_contents($path),
                                new StorageMetadata(
                                    pathinfo($path, \PATHINFO_EXTENSION),
                                    mime_content_type($path),
                                    new ArrayCollection(),
                                    new ArrayCollection()
                                )
                            )
                        );
                        $link->setFile($storage);

                        if (!empty($row['credits'])) {
                            $link->setCredits($row['credits']);
                        }

                        $imageAltName = str_replace('_src', '_alt', $name);
                        if (!empty($row[$imageAltName])) {
                            // $link->setDescription($row[$imageAltName]);
                        }

                        $documentManager->flush();
                    } else {
                        $result['warnings'][] = 'File not found: ' . $path . ' for ' . $newObject->getTitle(
                            );
                    }
                }

                $relation2->addReference($link);
            }

            $newObject->addRelation($relation2);
        }
    }

    public static function processMetaField($mappedField, $value, $newObject)
    {
        if (trim($value) != '') {
            $newObject->getMetadata()->set($mappedField, $value);
        }
    }

    public static function processConnectorField($mappedField, $value, $newObject, $entityManager)
    {
        $connectorId = str_replace('connector-', '', $mappedField);
        $connectorConfig = $entityManager->getRepository(Config::class)->find($connectorId);

        $connector = new Connector();
        $connector->setConfigId($connectorId);
        $connector->setConfigAdapter($connectorConfig->getAdapter());
        $connector->setExternalId($value);

        $newObject->addConnector($connector);
    }


}
