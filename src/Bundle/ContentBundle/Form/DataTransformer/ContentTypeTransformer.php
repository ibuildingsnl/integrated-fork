<?php

namespace Integrated\Bundle\ContentBundle\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;

class ContentTypeTransformer implements DataTransformerInterface
{
    public function __construct(private readonly ManagerRegistry $mr, private readonly string $className) {
    }

    /**
     * DB data -> Form data
     *
     * @param $objects
     *
     * @return array|object[]
     */
    public function transform($objects): array {
        if ($objects === null) {
            return [];
        }

        $ids = [];

        foreach($objects as $object) {
            $ids[] = $object->getId();
        }

        return $ids;
    }

    /**
     * Form data -> DB Data
     *
     * @param $value
     *
     * @return array|object[]
     */
    public function reverseTransform($value) {
        if(!isset($value['selections'])) {
            return [];
        }

        $repo = $this->mr->getRepository($this->className);

        $results = $repo->findBy(['_id' => ['$in' => $value['selections']]]);

        return $results;
    }
}