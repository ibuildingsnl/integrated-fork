<?php

namespace Integrated\Bundle\ImportBundle\Import\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ImportBundle\Import\Create\Create;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;

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
            'end_date' => 'formatDateString',
        ];

        foreach ($dateFields as $field => $method) {
            if (isset($newData[$field])) {
                $newData[$field] = self::$method($newData[$field]);
            }
        }
    }

    public static function setPublished($newData, $newObject)
    {
        if (\array_key_exists('published', $newData)) {
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
            // TODO: this seems highly specific and not usable in general
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

    public static function processImageElements(
        $html,
        $newObject,
        $newData,
        $importDefinition,
        $documentManager,
        $storageManager
    ) {
        $result = ExecuteImporter::initializeResult();

        $tags = ['a', 'img'];
        foreach ($tags as $tag) {
            foreach ($html->find($tag) as $element) {
                if (!$importDefinition->getImageContentType() || !$importDefinition->getImageRelation()) {
                    continue;
                }

                $href = ($tag == 'a') ? $element->href : $element->src;
                $title = ($tag == 'a') ? false : $element->title;

                if (strpos($href, '/') === 0) {
                    if (!$importDefinition->getWebsiteBaseUrl()) {
                        continue;
                    }
                    $href = rtrim($importDefinition->getWebsiteBaseUrl(), '/') . $href;
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

                if (strlen($element->alt) > 0) {
                    $newData['Image Description'] = $element->alt;
                }

                if (strlen($element->title) > 0) {
                    $newData['Image Title'] = $element->title;
                }

                if ($href) {
                    $checkResult = Create::createFileFromUrl(
                        $href,
                        $newObject,
                        $newData,
                        $importDefinition,
                        $storageManager,
                        $documentManager,
                        $title
                    );

                    $image = $checkResult['file'];

                    $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);

                    if ($image === false) {
                        continue;
                    }

                    $relation = new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation();
                    $relationType = $tag == 'img' ? 'embedded' : $importDefinition->getImageRelation()->getType();
                    $relationId = $tag == 'img' ? '__editor_image' : $importDefinition->getImageRelation()->getId();

                    $skipImage = $tag == 'img' && $newObject->getReferencesByRelationType('embedded') && array_search(
                                                                                                             $image->getId(
                                                                                                             ),
                                                                                                             array_column(
                                                                                                                 $newObject->getReferencesByRelationType(
                                                                                                                     'embedded'
                                                                                                                 ),
                                                                                                                 'id'
                                                                                                             )
                                                                                                         ) !== false;

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

        return [
            'html' => $html,
            'result' => $result,
        ];
    }

    public static function fieldProcessor(
        $mappedField,
        $value,
        $newObject,
        $importDefinition,
        $documentManager,
        $storageManager
    ) {
        $result = ExecuteImporter::initializeResult();

        if (strpos($mappedField, 'author-') === 0) {
            self::processAuthorField($mappedField, $value, $newObject, $importDefinition, $documentManager);
        }

        if (strpos($mappedField, 'meta-') === 0) {
            self::processMetaField($mappedField, $value, $newObject);
        }

        if (strpos($mappedField, 'relation-') === 0) {
            $checkResult = self::processRelationField(
                $mappedField,
                $value,
                $newObject,
                $importDefinition,
                $documentManager,
                $storageManager
            );
            $result['messages'] = array_merge($result['messages'], $checkResult['messages']);
        }

        return $result;
    }

    public static function processMetadata(
        $row,
        $newData,
        $newObject,
        $importDefinition,
        $importType,
        $documentManager,
        $storageManager
    ) {
        $result = ExecuteImporter::initializeResult();

        if ($importType === 'WordPress') {
            $checkResult = WP::processWpMetadata(
                $row,
                $newData,
                $newObject,
                $importDefinition,
                $documentManager,
                $storageManager
            );
            $newObject = $checkResult['newObject'];
            $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);
        }

        if (isset($row['contentitem_id'])) {
            $newObject->getMetadata()->set('externalId', $row['contentitem_id']);
            $newObject->getMetadata()->set('importDate', date('Ymd'));
            $newObject->getMetadata()->set('importWebsiteBaseUrl', $importDefinition->getWebsiteBaseUrl());
        }

        // //this makes no sense yet..
        if ($newObject->getMetadata()->get('premium') == 1) {
            $newObject->isPremium(true);
        }

//        return $newObject;
        return [
            'result' => $result,
            'newObject' => $newObject,
        ];
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
                            $importDefinition->getWebsiteBaseUrl(),
                            $importDefinition->getAuthorContentType(),
                            $documentManager
                        )
                    );
                    $newObject->addAuthor($author);
                }
            }
        }
    }

    public static function processRelationField(
        $mappedField,
        $value,
        $newObject,
        $importDefinition,
        $documentManager,
        $storageManager
    ) {
        $result = ExecuteImporter::initializeResult();

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
            $newRelation = new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation();
            $newRelation->setRelationId($relation->getId());
            $newRelation->setRelationType($relation->getType());

            if (!\is_array($value)) {
                $value = explode(',', $value);
            }

            foreach ($value as $valueName) {
                $valueName = html_entity_decode($valueName);
                $contentTypeFields = $targetContentType->getFields()->toArray();
                $parentId = false;
                foreach ($contentTypeFields as $contentTypeField) {
                    if ($contentTypeField->getName() === 'parent_id') {
                        $parentId = true;
                    }
                }

                $existingDocument = false;

                if ($targetContentType->getClass() == Taxonomy::class && $parentId) {
                    foreach ($importDefinition->getChannels() as $channel) {
                        // TODO: Move to seperate function
                        $title = str_ireplace(' Website', '', $channel->getName());
                        if (!$parent = $documentManager->getRepository(Content::class)
                                                       ->createQueryBuilder()->select()
                                                       ->field('title')->equals($title)
                                                       ->field('parent_id')->exists(false)
                                                       ->limit(1)->getQuery()
                                                       ->getSingleResult()) {
                            $parent = $targetContentType->create();
                            $parent->setTitle($title);
                            $parent->setSlug($title);
                            $parent->addChannel($channel);
                            $parent->setDisabled(true);

                            $result['messages'][] = "[INFO] Parent Dossier created with the name: {$title}";

                            $documentManager->persist($parent);
                            $documentManager->flush();
                        }

                        if ($parent) {
                            $result['messages'][] = '[INFO] Parent ' . $targetContentType . ' found for: ' . $title . ' - Taxonomy (' . $valueName . ') will be linked to it';
                        }

                        $valueName = trim($valueName);

                        $existingDocument = $documentManager->getRepository(Content::class)->findOneBy(
                            [
                                'title' => $valueName,
                                'contentType' => $targetContentType->getId(),
                                'parent_id' => $parent->getId(),
                            ]
                        );

                        if (!$existingDocument) {
                            $existingDocument = $targetContentType->create();

                            if (strpos($valueName, 'http') !== false) {
                                $existingDocument->setTitle(basename($valueName)); // not sure we need this
                            } else {
                                $existingDocument->setTitle($valueName);
                            }

                            $existingDocument->setParentId($parent->getId());
                            $existingDocument->getMetadata()->set('importDate', date('Ymd'));
                            $existingDocument->getMetadata()->set('externalId', $valueName);
                            $existingDocument->getMetadata()->set(
                                'importWebsiteBaseUrl',
                                $importDefinition->getWebsiteBaseUrl()
                            );

                            $existingDocument->addChannel($channel);

                            $documentManager->persist($existingDocument);
                            $documentManager->flush();
                        }
                    }
                } else {
                    $valueName = trim($valueName);
                    if ($targetContentType->getClass() == Taxonomy::class || $targetContentType->getClass(
                        ) == Article::class) {
                        $existingDocument = $documentManager->getRepository(Content::class)->findOneBy(
                            ['title' => $valueName, 'contentType' => $targetContentType->getId()]
                        );
                    }

                    if (!$existingDocument) {
                        $existingDocument = $targetContentType->create();
                        if (strpos($valueName, 'http') !== false) {
                            $existingDocument->setTitle(basename($valueName));
                        } else {
                            $existingDocument->setTitle($valueName);
                        }
                        $existingDocument->getMetadata()->set('importDate', date('Ymd'));
                        $existingDocument->getMetadata()->set('externalId', $valueName);
                        $existingDocument->getMetadata()->set(
                            'importWebsiteBaseUrl',
                            $importDefinition->getWebsiteBaseUrl()
                        );

                        foreach ($importDefinition->getChannels() as $channel) {
                            $existingDocument->addChannel($channel);
                        }

                        $result['messages'][] = "[INFO] {$targetContentType} created with the name {$valueName}";

                        $documentManager->persist($existingDocument);
                        $documentManager->flush();
                    }
                }
                // TODO: This needs to be made better
                if ($existingDocument instanceof Image || $existingDocument instanceof File) {
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
                        $existingDocument->setFile($storage);

                        if (!empty($row['credits'])) {
                            $existingDocument->setCredits($row['credits']);
                        }

                        $documentManager->flush();
                    } else {
                        $result['messages'][] = '[WARNING] File not found: ' . $path . ' for ' . $newObject->getTitle();
                    }
                }

                $newRelation->addReference($existingDocument);
            }

            $newObject->addRelation($newRelation);
        }

        return $result;
    }

    public static function processMetaField($mappedField, $value, $newObject)
    {
        if (trim($value) != '') {
            $newObject->getMetadata()->set($mappedField, $value);
        }
    }

    public static function setObjectProperties(
        $newData,
        $newObject,
        $importDefinition,
        $storageManager,
        $documentManager,
        $importType
    ) {
        $result = ExecuteImporter::initializeResult();

        foreach ($newData as $field => $value) {
            if ($field == 'created_at' || $field == 'updated_at' || $field == 'publish_time.start_date' || $field == 'publish_time.end_date') {
                continue;  // Skip processing for the specified fields
            }

            $method = str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));

            // Prefix with 'set' for setter methods, e.g., 'Title' becomes 'setTitle'
            $setterMethod = 'set' . $method;

            // Special handling for boolean fields
            if (\in_array($field, ['featured', 'premium'])) {
                $setterMethod = 'is' . ucfirst($field);
            }

            if ($field === 'parent_id' && strlen($value) != 32) {
                if (method_exists($newObject, 'setParentId')) {
                    \call_user_func([$newObject, 'setParentId'], $value);
                }
            }

            if ($field === 'featured_image') {
                // TODO: Add more options for example Drupal
                $href = '';
                if (preg_match('/^[0-9]+$/', $value)) {
                    if ($importType === 'WordPress') {
                        $href = $importDefinition->getWebsiteBaseUrl() . '?attachment_id=' . $value;
                    }
                } else {
                    if (filter_var($value, \FILTER_VALIDATE_URL) !== false) {
                        $href = $value;
                    } else {
                        $result['messages'][] = "[WARNING] {$field} {$value} does not contain a valid link or id";
                    }
                }

                if ($href === '') {
                    $result['messages'][] = "[WARNING] {$field} {$value} does not contain a valid link or id";

                    return $result;
                }

                $checkResult = Create::createFileFromUrl(
                    $href,
                    $newObject,
                    $newData,
                    $importDefinition,
                    $storageManager,
                    $documentManager,
                    false,
                    true
                );
                $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);
                $newObject->setFeaturedImage($checkResult['file']);
                continue;
            }

            if (method_exists($newObject, $setterMethod)) {
                \call_user_func([$newObject, $setterMethod], $value);
            }
        }

        return $result;
    }

    public static function checkForExistingContent($importDefinition, $row, $newData, $documentManager)
    {
        $result = ExecuteImporter::initializeResult();
        $target = null;
        // TODO: contentitem_id is an integrated id field
        if ($importDefinition->getWebsiteBaseUrl()) {
            $fields = [
                'contentitem_id' => 'contentitem_id',
                'wp:post_id' => 'wpPostId',
                'id' => 'PostId',
            ];

            foreach ($fields as $field => $dbField) {
                if (isset($row[$field])) {
                    $doubleArticle = $documentManager
                        ->getRepository(Content::class)
                        ->findOneBy(
                            [
                                "metadata.data.{$dbField}" => $row[$field],
                                'metadata.data.importWebsiteBaseUrl' => $importDefinition->getWebsiteBaseUrl(),
                            ]
                        );
                    if ($doubleArticle) {
                        $result['messages'][] = "[UPDATING] Item ID {$row[$field]} already imported - updating";
                        $target = $doubleArticle;
                    }
                }
            }
        }

        foreach ($importDefinition->getChannels() as $channel) {
            $parent = $documentManager->getRepository(Content::class)
                                      ->createQueryBuilder()->select()
                                      ->field('title')->equals($newData['title'])
                                      ->field('channels.$id')->equals($channel->getId())
                                      ->limit(1)->getQuery()
                                      ->getSingleResult();
            if ($parent) {
                $result['messages'][] = "[UPDATING] {$importDefinition->getContentType()} with title: {$newData['title']} for {$channel->getName()} already imported - updating";
                $target = $parent;
            }
        }

        return [
            'target' => $target,
            'result' => $result,
        ];
    }

    public static function authorProcessor(
        $row,
        $newObject,
        $importDefinition,
        $documentManager
    ) {
        $result = ExecuteImporter::initializeResult();

        if (array_key_exists('Author ID', $row)) {
            $targetContentType = $documentManager->find(ContentType::class, $importDefinition->getAuthorContentType());
            $authorId = $row['Author ID'];
            $firstName = $row['Author First Name'];
            $lastName = $row['Author Last Name'];
            $email = $row['Author Email'];
            $baseUrl = $importDefinition->getWebsiteBaseUrl();

            $person = $documentManager
                ->getRepository(Content::class)
                ->createQueryBuilder()->select()
                ->field('contentType')->equals($targetContentType->getId())
                ->field('firstName')->equals($firstName)
                ->field('lastName')->equals($lastName)
                ->field('metadata.data.importWebsiteBaseUrl')->equals($baseUrl)
                ->field('metadata.data.wpAuthorId')->equals($authorId)
                ->limit(1)->getQuery()
                ->getSingleResult();

            if (!$person) {
                $person = $documentManager
                    ->getRepository(Content::class)
                    ->createQueryBuilder()->select()
                    ->field('contentType')->equals($targetContentType->getId())
                    ->field('firstName')->equals($firstName)
                    ->field('lastName')->equals($lastName)
                    ->limit(1)->getQuery()
                    ->getSingleResult();
            } else {
                $result['messages'][] = "[NOTICE] Author {$firstName} {$lastName} was found and will be used as author";
            }

            if (!$person) {
                $person = $targetContentType->create();

                $person->setFirstname($firstName);
                $person->setLastname($lastName);
                $person->setEmail($email);
                $person->getMetadata()->set('wpAuthorId', $authorId);
                $person->getMetadata()->set('importWebsiteBaseUrl', $baseUrl);

                $documentManager->persist($person);

                $result['messages'][] = "[NOTICE] Author {$firstName} {$lastName} has been created and will be used as author";
            }

            $author = new Author();
            $author->setPerson($person);
            $newObject->addAuthor($author);
        } else {
            $result['messages'][] = "[WARNING] Author ID was not found, no author will be linked.";
        }

        return $result;
    }
}
