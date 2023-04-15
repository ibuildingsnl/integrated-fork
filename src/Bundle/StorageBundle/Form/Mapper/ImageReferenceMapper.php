<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Form\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;

class ImageReferenceMapper implements DataMapperInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly array $channels
    ) {
    }

    public function mapDataToForms($viewData, \Traversable $forms): void
    {
        if (!$viewData instanceof Image && $viewData !== null) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['image']->setData($viewData?->getFile());
    }

    public function mapFormsToData(\Traversable $forms, &$viewData): void
    {
        /** @var FormInterface[] $form */
        $form = iterator_to_array($forms);

        $data = $form['image']->getData();

        if (!$data instanceof StorageInterface) {
            $viewData = null;

            return;
        }

        if (!$viewData instanceof Image) {
            $viewData = $this->newImage($data);

            return;
        }

        if ($data->getIdentifier() === $viewData->getFile()->getIdentifier()) {
            return;
        }

        $viewData = $this->newImage($data);
    }

    private function newImage(StorageInterface $storage): Image
    {
        $image = new Image();

        $image->setContentType('image');
        $image->setTitle($storage->getIdentifier());
        $image->setFile($storage);
        if (!empty($this->channels)) {
            $image->setChannels(new ArrayCollection($this->channels));
        }

        $this->manager->persist($image);

        return $image;
    }
}
