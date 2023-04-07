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
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;

class FileReferenceMapper implements DataMapperInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly array $channels
    ) {
    }

    public function mapDataToForms($viewData, \Traversable $forms): void
    {
        if (!$viewData instanceof File && $viewData !== null) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['file']->setData($viewData?->getFile());
    }

    public function mapFormsToData(\Traversable $forms, &$viewData): void
    {
        /** @var FormInterface[] $form */
        $form = iterator_to_array($forms);

        $data = $form['file']->getData();

        if (!$data instanceof StorageInterface) {
            $viewData = null;

            return;
        }

        if (!$viewData instanceof File) {
            $viewData = $this->newFile($data);

            return;
        }

        if ($data->getIdentifier() === $viewData->getFile()->getIdentifier()) {
            return;
        }

        $viewData = $this->newFile($data);
    }

    private function newFile(StorageInterface $storage): File
    {
        $file = new File();

        $file->setContentType('file');
        $file->setTitle($storage->getIdentifier());
        $file->setFile($storage);
        if (!empty($this->channels)) {
            $file->setChannels(new ArrayCollection($this->channels));
        }

        $this->manager->persist($file);

        return $file;
    }
}
