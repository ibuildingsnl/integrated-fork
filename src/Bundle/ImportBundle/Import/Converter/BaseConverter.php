<?php

namespace Integrated\Bundle\ImportBundle\Import\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;

class BaseConverter
{
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
        if (isset($newData['created_at'])) {
            $newData['created_at'] = self::formatDateTime($newData['created_at']);
        }

        if (isset($newData['updated_at'])) {
            $newData['updated_at'] = self::formatDateTime($newData['updated_at']);
        }

        if (isset($newData['start_date'])) {
            $newData['start_date'] = self::formatDateString($newData['start_date']);
        }

        if (isset($newData['end_date'])) {
            $newData['end_date'] = self::formatDateString($newData['end_date']);
        }
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

    public static function processInlineImages($html, $newObject, $importDefinition, $documentManager, $storageManager)
    {
        $title = false;
        foreach ($html->find('img') as $img) {
            if (!$importDefinition->getImageContentType() || !$importDefinition->getImageRelation()) {
                continue;
            }

            $title = $img->title;
            $href = $img->src;
            if (strpos($href, '/') === 0) {
                if (!$importDefinition->getImageBaseUrl()) {
                    continue;
                }
                $href = rtrim($importDefinition->getImageBaseUrl(), '/') . $href;
            }

            if (strpos($href, '../../upload/') === 0) {
                $href = str_replace('../../upload/', '', $href);
                //TODO:Seems to be for a specific case?
                foreach ($importDefinition->getChannels() as $channel) {
                    if (file_exists('/home/testpi-integrated/importfiles/' . $channel->getId() . '/' . $href)) {
                        $href = '/home/testpi-integrated/importfiles/' . $channel->getId() . '/' . $href;
                    }
                }
            }

            /*
             * Wordpress
             */
            $image = $documentManager->getRepository(Image::class)->findOneBy(
                [
                    'metadata.data.wpUrl' => $href,
                    'metadata.data.importImageBaseUrl' => $importDefinition->getImageBaseUrl(),
                ]
            );
            if ($image) {
                // attach existing images instead of duplication

                $skipImage = false;
                if ($newObject->getReferencesByRelationType('embedded')) {
                    foreach ($newObject->getReferencesByRelationType('embedded') as $reference) {
                        if ($reference->getId() == $image->getId()) {
                            $skipImage = true;
                        }
                    }
                }

                if (!$skipImage) {
                    $relation = new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation();
                    $relation->setRelationId('__editor_image');
                    $relation->setRelationType('embedded');
                    $relation->addReference($image);
                    $newObject->addRelation($relation);
                }

                $img->outertext = '<img src="/storage/' . $image->getId() . '.jpg" class="img-responsive"'
                                  . ' title="' . htmlspecialchars($image->getTitle()) . '" alt="' . htmlspecialchars(
                                      $image->getTitle()
                                  ) . '"'
                                  . ' data-integrated-id="' . $image->getId() . '" />';
                continue;
            }

            if (!$title) {
                $title = basename($img->src);
                $title = str_replace('.png', '', $title);
                $title = str_replace('.jpg', '', $title);
                $title = str_replace('.jpeg', '', $title);
                $title = str_replace('.gif', '', $title);
            }

            $tmpfile = tempnam('/tmp/', 'img') . '.' . pathinfo($href, \PATHINFO_EXTENSION);
            file_put_contents($tmpfile, @file_get_contents($href));
            if (filesize($tmpfile) == 0) {
                // echo $file . "\n";
                // echo "FILE HAS 0 BYTES\n";
                unlink($tmpfile);
                continue;
            }

            $storage = $storageManager->write(
                new MemoryReader(
                    file_get_contents($tmpfile),
                    new StorageMetadata(
                        pathinfo($href, \PATHINFO_EXTENSION),
                        mime_content_type($tmpfile),
                        new ArrayCollection(),
                        new ArrayCollection()
                    )
                )
            );

            $imageContentType = $importDefinition->getImageContentType();

            $image = $documentManager->getRepository(Image::class)->findOneBy(
                [
                    'contentType' => $imageContentType,
                    'file.identifier' => $storage->getIdentifier(),
                ]
            );

            if (!$image) {
                $newImage = new Image();
                $newImage->setContentType('image');

                $documentManager->persist($newImage);

                $newImage->setFile($storage);
                $newImage->setContentType($imageContentType);
                $newImage->setTitle($title);
                $newImage->getMetadata()->set('importDate', date('Ymd'));

                $image = $newImage;

                $documentManager->flush();
            }

            $skipImage = false;
            if ($newObject->getReferencesByRelationType('embedded')) {
                foreach ($newObject->getReferencesByRelationType('embedded') as $reference) {
                    if ($reference->getId() == $image->getId()) {
                        $skipImage = true;
                    }
                }
            }

            if (!$skipImage) {
                $relation = new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation();
                $relation->setRelationId('__editor_image');
                $relation->setRelationType('embedded');
                $relation->addReference($image);
                $newObject->addRelation($relation);
            }

            $img->outertext = '
            <img src="/storage/' . $image->getId() . '.jpg"
            class="img-responsive"
            title="' . htmlspecialchars($title) . '"
            alt="' . htmlspecialchars($title) . '"
            data-integrated-id="' . $image->getId() . '" />';
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
    
    public static function fieldMapper($row) {
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
    }
    
    public static function checkForExistingContent($field, $value, $importDefinition, $documentManager)
    {
        $doubleArticle = $documentManager
            ->getRepository(Content::class)
            ->findOneBy(
                [
                    "metadata.data.{$field}" => $value,
                    'metadata.data.importImageBaseUrl' => $importDefinition->getImageBaseUrl(),
                ]
            );

        return $doubleArticle;
    }
}
