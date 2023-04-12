<?php

namespace Integrated\Bundle\StorageBundle\Form\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Content\Document\Storage\FileInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;

abstract class StorageReferenceMapper implements DataMapperInterface
{
    protected const CLASSNAME = File::class;
    protected const FORM_FIELD = 'image';

    public function mapDataToForms($viewData, \Traversable $forms): void
    {
        if ($viewData !== null && !get_class($viewData) == static::CLASSNAME) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms[static::FORM_FIELD]->setData($viewData?->getFile());
    }

    public function mapFormsToData(\Traversable $forms, &$viewData): void
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $data = $forms[static::FORM_FIELD]->getData();

        if (!$data instanceof StorageInterface) {
            $viewData = null;

            return;
        }

        if (!$viewData || get_class($viewData) != static::CLASSNAME) {
            $viewData = $this->newFile($data);

            return;
        }

        if ($data->getIdentifier() === $viewData->getFile()->getIdentifier()) {
            return;
        }

        $viewData = $this->newFile($data);
    }

    protected abstract function newFile(StorageInterface $storage): FileInterface;
}
