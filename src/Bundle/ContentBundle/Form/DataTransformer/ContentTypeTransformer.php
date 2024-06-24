<?php

namespace Integrated\Bundle\ContentBundle\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;

class ContentTypeTransformer implements DataTransformerInterface
{
    public function __construct(private readonly ManagerRegistry $mr, private readonly string $className)
    {
    }

    /**
     * DB data -> Form data.
     *
     * @return array|object[]
     */
    public function transform($value): array
    {
        if ($value === null) {
            return [];
        }

        $ids = [];

        foreach ($value as $object) {
            $ids[] = $object->getId();
        }

        return $ids;
    }

    /**
     * Form data -> DB Data.
     *
     * @return array|object[]
     */
    public function reverseTransform($value): array
    {
        if (!isset($value['selections'])) {
            return [];
        }

        $repo = $this->mr->getRepository($this->className);

        $results = $repo->findBy(['_id' => ['$in' => $value['selections']]]);

        return $results;
    }
}
