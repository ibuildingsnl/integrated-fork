<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Jurre de Jongh <jurre@e-active.nl>
 */
class AuthorTransformer implements DataTransformerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $mr;

    /**
     * AuthorTransformer constructor.
     */
    public function __construct(ManagerRegistry $mr)
    {
        $this->mr = $mr;
    }

    public function transform($arrayCollection): mixed
    {
        if ($arrayCollection == null) {
            return [];
        }

        $collection = [];

        $authorNameCount = [];
        foreach ($arrayCollection as $author) {
            if (!($author instanceof Author) || !$author->getPerson()) {
                continue;
            }
            $authorNameCount[] = (string) $author->getPerson();
        }
        $authorNameCount = array_count_values($authorNameCount);

        foreach ($arrayCollection as $author) {
            if (!($author instanceof Author) || !$author->getPerson()) {
                continue;
            }

            $name = (string) $author->getPerson();
            if ($authorNameCount[$name] > 1) {
                $name .= ' ('.$author->getPerson()->getContentType().')';
            }

            $collection[] = [
                'id' => $author->getPerson()->getId(),
                'text' => $name,
                'type' => $author->getType(),
            ];
        }

        return $collection;
    }

    public function reverseTransform($array): mixed
    {
        $mr = $this->mr->getManager();
        $collection = [];

        if (\is_array($array) && isset($array['persons'], $array['types']) && \is_array($array['types'])) {
            $repo = $mr->getRepository(Person::class);
            $persons = $repo->findBy(['_id' => ['$in' => $array['persons']]]);

            foreach ($persons as $person) {
                if ($person && isset($array['types'][$person->getId()])) {
                    $author = new Author();
                    $author->setType($array['types'][$person->getId()]);
                    $author->setPerson($person);

                    $collection[] = $author;
                }
            }
        }

        return new ArrayCollection($collection);
    }
}
