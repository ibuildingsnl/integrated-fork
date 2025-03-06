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
use Integrated\Bundle\ContentBundle\Document\Content\File;
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

    public function transform($list): mixed
    {
        if (\is_array($list)) {
            $data = [];
            foreach ($list as $item) {
                if ($item instanceof File || \count($item) > 0) {
                    if (\is_array($item)) {
                        $data[] = $item['$id'];
                    } else {
                        $data[] = $item->getId();
                    }
                }
            }

            return implode(',', $data);
        }

        return $list;
    }

    public function reverseTransform($input): mixed
    {
        $ids = explode(',', $input);
        $files = [];
        foreach ($ids as $id) {
            $files[] = $this->repository->find($id);
        }

        return array_filter($files);
    }
}
