<?php

namespace Integrated\Bundle\ImportBundle\Import\Create;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata as StorageMetadata;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
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
            ->field('metadata.data.importImageBaseUrl')->equals($baseUrl)
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
     * @param String $href
     * @return StorageInterface|void
     */
    public static function createFileFromUrl($href, $newObject, $importDefinition, $storageManager, $documentManager, $title = false, $setFeatured = false) {

        $href = self::maybeFetchRedirectUrl($href);

        $tmpfile = tempnam('/tmp/', 'img') . '.' . pathinfo($href, \PATHINFO_EXTENSION);
        file_put_contents($tmpfile, @file_get_contents($href));
        if (filesize($tmpfile) == 0) {
            unlink($tmpfile);
            return false;
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
            $title = parse_url($href, PHP_URL_PATH);
            $title = basename($title);
        }

        $imageContentType = $importDefinition->getImageContentType();

        $image = $documentManager->getRepository(Image::class)->findOneBy(
            [
                'contentType' => $imageContentType,
                'file.identifier' => $storage->getIdentifier(),
            ]
        );
        //TODO: Add support for description
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
        if ($setFeatured === true) {
            $newObject->setFeaturedImage($image);
            $documentManager->flush();
        }

        return $image;
    }

    private static function maybeFetchRedirectUrl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);  // Enable header (not strictly necessary)

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            return;
        }

        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);  // Get the final URL after all redirects
        curl_close($ch);

        return $effectiveUrl;
    }
}
