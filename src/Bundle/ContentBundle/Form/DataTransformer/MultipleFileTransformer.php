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

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class MultipleFileTransformer implements DataTransformerInterface
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    public function __construct(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($list)
    {
        if (\is_array($list)) {
            return implode(',', array_map(fn ($file) => $file->getId(), $list));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($input)
    {
        $ids = explode(',', $input);
        $files = [];
        foreach ($ids as $id) {
            $files[] = $this->repository->find($id);
        }

        return array_filter($files);
    }
}
