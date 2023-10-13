<?php

namespace Integrated\Bundle\ImportBundle\Import\Create;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
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
            $dm->flush($person);
        }

        return $person;
    }

    /**
     * @param string $href
     *
     * @return StorageInterface|void
     */
    public static function createFileFromUrl($href, $newObject, $newData, $importDefinition, $storageManager, $documentManager, $title = false, $setFeatured = false)
    {
        // TODO: This needs to be made more dynamic for File and Image type.
        $result = ExecuteImporter::initializeResult();

        $href = self::maybeFetchRedirectUrl($href);

        $extension = pathinfo($href, \PATHINFO_EXTENSION);

        if ($extension === '') {
            return [
                'file' => false,
                'result' => $result,
            ];
        }

        $tmpfile = tempnam('/tmp/', 'img').'.'.pathinfo($href, \PATHINFO_EXTENSION);
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

        if (!$title) {
            $title = parse_url($href, \PHP_URL_PATH);
            $title = basename($title);
            $title = str_replace('.'.pathinfo($href, \PATHINFO_EXTENSION), '', $title);
        }

        if (\in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
            $targetContentType = $documentManager->find(ContentType::class, $importDefinition->getImageContentType());
            $contentType = $importDefinition->getImageContentType();
        } else {
            $targetContentType = $documentManager->find(ContentType::class, $importDefinition->getFileContentType());
            $contentType = $importDefinition->getFileContentType();
        }

        // TODO: Add support for description
        if (!$file = $documentManager->getRepository(Content::class)
                                     ->createQueryBuilder()->select()
                                     ->field('contentType')->equals($contentType)
                                     ->field('file.identifier')->equals($storage->getIdentifier())
                                     ->limit(1)->getQuery()
                                     ->getSingleResult()) {
            $newFile = $targetContentType->create();

            $documentManager->persist($newFile);

            $newFile->setFile($storage);
            $newFile->setTitle($title);
            $newFile->getMetadata()->set('importDate', date('Ymd'));

            if (array_key_exists('Image Title', $newData) && strlen($newData['Image Title']) > 0) {
                $newFile->setTitle($newData['Image Title']);
            }

            if (array_key_exists('Image Description', $newData) && strlen($newData['Image Description']) > 0) {
                $newFile->setDescription($newData['Image Description']);
            }

            $file = $newFile;

            $result['messages'][] = "[INFO] Image imported with title {$title}";

            $documentManager->flush();
        } else {
            $result['messages'][] = "[INFO] Image {$title} already existed";
        }
        if ($setFeatured === true) {
            $newObject->setFeaturedImage($file);

            $result['messages'][] = "[INFO] Image {$title} set as featured image";
            $documentManager->flush();
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

            if (strlen($title) > 0 && !$parent = $documentManager->getRepository(Content::class)
                                           ->createQueryBuilder()->select()
                                           ->field('title')->equals($title)
                                           ->field('parent_id')->equals($parentBrandTaxonomy->getId())
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
                $result['messages'][] = '[INFO] Parent ' . $parent->getTitle() . ' found for: ' . $title . ' - Taxonomy (' . $title . ') will be linked to it';
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
        $ch = curl_init();

        curl_setopt($ch, \CURLOPT_URL, $url);
        curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_HEADER, true);  // Enable header (not strictly necessary)

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: '.curl_error($ch);
            curl_close($ch);

            return;
        }

        $effectiveUrl = curl_getinfo($ch, \CURLINFO_EFFECTIVE_URL);  // Get the final URL after all redirects
        curl_close($ch);

        return $effectiveUrl;
    }
}
