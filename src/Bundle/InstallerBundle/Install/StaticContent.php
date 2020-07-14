<?php

namespace Integrated\Bundle\InstallerBundle\Install;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\StaticContent\StaticContentRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Migrations constructor.
     *
     * @param DocumentManager         $documentManager
     * @param StaticContentRepository $repository
     * @param SerializerInterface     $serializer
     */
    public function __construct(DocumentManager $documentManager, StaticContentRepository $repository, SerializerInterface $serializer)
    {
        $this->documentManager = $documentManager;
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    public function execute()
    {
        //$encoders = [new XmlEncoder()];
        //$normalizers = [new ObjectNormalizer()];

        //$serializer = new Serializer($normalizers, $encoders);

        //$rel = $this->documentManager->getRepository(Relation::class)->find('parent_company');
        //dump($this->serializer->serialize($rel, 'xml'));

        foreach ($this->repository->all() as $item) {
            $document = $this->documentManager->getRepository($item['class'])->find($item['id']);
            if ($document === null) {
                $document = $this->serializer->deserialize($item['fields-xml'], $item['class'], 'xml');

                if ($item['defaults-xml'] !== null) {
                    $document = $this->serializer->deserialize($item['defaults-xml'], $item['class'], 'xml', [AbstractNormalizer::OBJECT_TO_POPULATE => $document]);
                }

                $this->documentManager->persist($document);
            } else {
                $this->serializer->deserialize($item['fields-xml'], $item['class'], 'xml', [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $document,
                    ]);
            }

            $this->documentManager->flush();
        }
    }
}
