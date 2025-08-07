<?php

namespace Integrated\Bundle\ImportBundle\Import\Create;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ImportBundle\Import\Converter\ExecuteImporter;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;

class Create
{
    public static function addAuthor($name, $baseUrl, $type, $documentManager)
    {
        if ($type === null) {
            throw new \Exception('Please choose an author content type');
        }

        $dm = $documentManager;

        $name = trim($name);
        if (stripos($name, 'by ') === 0) {
            $name = substr($name, 3);
        }

        while (strpos($name, '  ') !== false) {
            $name = str_replace('  ', ' ', $name);
        }

        if (strpos($name, ' ') !== false) {
            list($firstname, $lastname) = explode(' ', $name, 2);
        } else {
            $firstname = '';
            $lastname = $name;
        }

        if (trim($lastname) == '') {
            $lastname = '(empty)';
            // throw new Exception('Empty lastname: ' . $name); //@todo: waarom?
        }

        $person = $dm
            ->createQueryBuilder(Person::class)
            ->field('contentType')->equals($type)
            ->field('firstName')->equals($firstname)
            ->field('lastName')->equals($lastname)
            ->field('metadata.data.importWebsiteBaseUrl')->equals($baseUrl)
            ->getQuery()
            ->getSingleResult();
        if (!$person) {
            $person = $dm
                ->createQueryBuilder(Person::class)
                ->field('contentType')->equals($type)
                ->field('firstName')->equals($firstname)
                ->field('lastName')->equals($lastname)
                ->getQuery()
                ->getSingleResult();
        }

        if (!$person) {
            $person = new Person();
            $person
                ->setContentType($type)
                ->setFirstname($firstname)
                ->setLastname($lastname);

            $dm->persist($person);
            $dm->flush();
        }

        return $person;
    }

    /**
     * @param string $href
     *
     * @return StorageInterface|void
     */
    public static function createFileFromUrl($href, $newObject, $newData, $importDefinition, $storageManager, $documentManager, $title = false, $setFeatured = false) {
        $result = ExecuteImporter::initializeResult();

        try {
            $checkResult = self::maybeFetchRedirectUrl($href);
        } catch (\Exception $e) {
            $result['errors'][] = 'Item ' . (string)$newObject . ' failed: ' . $e->getMessage() . ' ' . nl2br(
                    $e->getTraceAsString()
                ) . ' ' . $e->getFile() . ' ' . $e->getLine();
        }

        $result['messages'] = array_merge($result['messages'], $checkResult['result']['messages']);
        $href = $checkResult['url'];

        $hrefWithoutQuery = strtok($href, '?');
        $extension = pathinfo($hrefWithoutQuery, PATHINFO_EXTENSION);

        if ($extension === '' || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'pdf'])) {
            $result['messages'][] = "[INFO] No valid image or file has been found at {$hrefWithoutQuery}";

            return [
                'file' => false,
                'result' => $result,
            ];
        }

        $tmpfile = tempnam('/tmp/', 'img') . '.' . pathinfo($hrefWithoutQuery, \PATHINFO_EXTENSION);
        file_put_contents($tmpfile, @file_get_contents($href));
        if (filesize($tmpfile) == 0) {
            unlink($tmpfile);
            $result['messages'][] = "[INFO] Image {$href} has 0 bites";

            return [
                'file' => false,
                'result' => $result,
            ];
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

        unlink($tmpfile);

        if (!$title) {
            $title = parse_url($href, \PHP_URL_PATH);
            $title = basename($title);
            $title = str_replace('.' . pathinfo($href, \PATHINFO_EXTENSION), '', $title);
        }

        if (\in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
            $targetContentType = $documentManager->find(ContentType::class, $importDefinition->getImageContentType());
            $contentType = $importDefinition->getImageContentType();
        } else {
            $targetContentType = $documentManager->find(ContentType::class, $importDefinition->getFileContentType());
            $contentType = $importDefinition->getFileContentType();
        }

        if (!$file = $documentManager->getRepository(Content::class)
                                     ->createQueryBuilder()->select()
                                     ->field('contentType')->equals($contentType)
                                     ->field('file.identifier')->equals($storage->getIdentifier())
                                     ->limit(1)->getQuery()
                                     ->getSingleResult()) {
            /** @var File $newFile */
            $newFile = $targetContentType->create();

            $relation = $documentManager->getRepository(Relation::class)->find('media_taxonomy');
            $newRelation = new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation();
            $newRelation->setRelationId($relation->getId());
            $newRelation->setRelationType($relation->getType());

            $documentManager->persist($newFile);

            foreach ($importDefinition->getChannels() as $channel) {
                $newFile->addChannel($channel);
            }

            $newFile->setFile($storage);
            $newFile->setTitle($title);
            $newFile->getMetadata()->set('importDate', date('Ymd'));
            $newFile->setChannels($importDefinition->getChannels());


            $mediaTaxonomy = $documentManager->find(ContentType::class, 'media_taxonomy');

            foreach ($importDefinition->getChannels() as $channel) {
                $brandName = str_ireplace(' Website', '', $channel->getName());
                if (!$parentBrandTaxonomy = $documentManager->getRepository(Content::class)
                                                            ->createQueryBuilder()->select()
                                                            ->field('title')->equals($brandName)
                                                            ->field('contentType')->equals('media_taxonomy')
                                                            ->field('parent_id')->exists(false)
                                                            ->limit(1)->getQuery()
                                                            ->getSingleResult()) {
                    $parentBrandTaxonomy = $mediaTaxonomy->create();
                    $parentBrandTaxonomy->setTitle($brandName);
                    $parentBrandTaxonomy->setSlug($brandName);
                    $parentBrandTaxonomy->addChannel($channel);
                    $parentBrandTaxonomy->setDisabled(true);

                    $result['messages'][] = "[INFO] Parent Brand Dossier created with the name: {$brandName}";

                    $documentManager->persist($parentBrandTaxonomy);
                    $documentManager->flush();
                }
                $newRelation->addReference($parentBrandTaxonomy);
            }

            if (\array_key_exists('Image Caption', $newData) && $newData['Image Caption'] !== '') {
                $caption = html_entity_decode($newData['Image Caption']);
                $newFile->setDescription($caption);
                $result['messages'][] = "[CAPTION] Trying to set Caption '{$caption}'";
            }

            $newFile->addRelation($newRelation);

            $file = $newFile;

            $result['messages'][] = "[INFO] {$targetContentType->getName()} imported with title {$title}";

            $documentManager->flush();
        } else {
            if (\array_key_exists('Image Caption', $newData) && $newData['Image Caption'] !== '') {
                $caption = html_entity_decode($newData['Image Caption']);
                $file->setDescription($caption);
                $result['messages'][] = "[CAPTION] Trying to set Caption '{$caption}'";
            }

            $result['messages'][] = "[INFO] {$targetContentType->getName()} {$title} already existed";
        }

        if ($setFeatured === true) {
            $newObject->setFeaturedImage($file);

            $result['messages'][] = "[INFO] {$targetContentType->getName()} {$title} set as featured image";
        }

        return [
            'file' => $file,
            'result' => $result,
        ];
    }

    public static function maybeCreateParent($title, $contentType, $documentManager, $importDefinition) {
        //TODO: This will be problematic when importing content into multiple channels.
        $result = ExecuteImporter::initializeResult();
        foreach ($importDefinition->getChannels() as $channel) {
            $brandName = str_ireplace(' Website', '', $channel->getName());
            if (!$parentBrandTaxonomy = $documentManager->getRepository(Content::class)
                                                        ->createQueryBuilder()->select()
                                                        ->field('title')->equals($brandName)
                                                        ->field('contentType')->equals($contentType->getId())
                                                        ->field('parent_id')->exists(false)
                                                        ->limit(1)->getQuery()
                                                        ->getSingleResult()) {
                $parentBrandTaxonomy = $contentType->create();
                $parentBrandTaxonomy->setTitle($brandName);
                $parentBrandTaxonomy->setSlug($brandName);
                $parentBrandTaxonomy->addChannel($channel);
                $parentBrandTaxonomy->setDisabled(true);

                $result['messages'][] = "[INFO] Parent Brand Dossier created with the name: {$brandName}";

                $documentManager->persist($parentBrandTaxonomy);
                $documentManager->flush();
            }

            $parent = false;

            if ($title !== '' && !$parent = $documentManager->getRepository(Content::class)
                                                                 ->createQueryBuilder()->select()
                                                                 ->field('title')->equals($title)
//                                                                 ->field('parent_id')->equals($parentBrandTaxonomy->getId())
                                                                 ->field('channels.$id')->equals($channel->getId())
                                                                 ->limit(1)->getQuery()
                                                                 ->getSingleResult()) {
                $parent = $contentType->create();
                $parent->setTitle($title);
                $parent->setSlug($title);
                $parent->setParentId($parentBrandTaxonomy->getId());
                $parent->addChannel($channel);
                $parent->setDisabled(true);

                $result['messages'][] = "[INFO] Parent Dossier created with the name: {$title}";

                $documentManager->persist($parent);
                $documentManager->flush();
            }

            if ($parent) {
                $result['messages'][] = '[INFO] Parent ' . $parent->getTitle(
                    ) . ' found for: ' . $title . ' - Taxonomy (' . $title . ') will be linked to it';
            }
        }
        return [
            'brandParent' => $parentBrandTaxonomy,
            'parent' => $parent,
            'result' => $result,
        ];
    }

    private static function maybeFetchRedirectUrl($url)
    {
        $result = ExecuteImporter::initializeResult();

        $ch = curl_init();

        curl_setopt($ch, \CURLOPT_URL, $url);
        curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_HEADER, true);  // Enable header (not strictly necessary)

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $result['messages'][] = "[WARNING] Curl error when trying to fetch: " . curl_error($ch);
            curl_close($ch);

            return [
                'url' => $url,
                'result' => $result,
            ];
        }

        $effectiveUrl = curl_getinfo($ch, \CURLINFO_EFFECTIVE_URL);  // Get the final URL after all redirects
        curl_close($ch);

        return [
            'url' => $effectiveUrl,
            'result' => $result,
        ];
    }
}
