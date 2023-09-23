<?php

namespace Integrated\Bundle\ImportBundle\Import\Create;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @param String $imageName
     * @return StorageInterface|void
     */
    public static function createFileFromImageName($imageName, $storageManager)
    {
        $filePath = 'public/files/' . $imageName;

        $tmpfile = tempnam("/tmp/", '');
        file_put_contents($tmpfile, @file_get_contents($filePath));

        if (filesize($tmpfile) == 0) {
            return;
        }

        $reader = new MemoryReader(
            file_get_contents($tmpfile),
            new \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage\Metadata(
                substr($imageName, strrpos($imageName, '.') + 1),
                mime_content_type($tmpfile),
                new ArrayCollection(),
                new ArrayCollection()
            )
        );

        return $storageManager->write($reader);
    }
}