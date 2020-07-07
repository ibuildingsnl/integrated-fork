<?php

namespace Integrated\Bundle\InstallerBundle\Install;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ContentBundle\StaticContent\StaticContentRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class StaticContent
{
    /**
     * @var StaticContentRepository
     */
    private $repository;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * Migrations constructor.
     *
     * @param DocumentManager         $documentManager
     * @param StaticContentRepository $repository
     */
    public function __construct(DocumentManager $documentManager, StaticContentRepository $repository)
    {
        $this->documentManager = $documentManager;
        $this->repository = $repository;
    }

    public function execute()
    {
        $encoders = [new XmlEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        foreach ($this->repository->all() as $item) {
            dump($item);
            $document = $this->documentManager->getRepository($item['class'])->find($item['id']);
            if ($document === null) {
                dump($document['fields']);
                $document = $serializer->deserialize($item['fields'], $item['class'], 'xml');

                $this->documentManager->persist($document);
            }

            $this->documentManager->flush();
        }
    }
}
