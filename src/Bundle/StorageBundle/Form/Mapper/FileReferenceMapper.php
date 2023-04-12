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

class FileReferenceMapper extends StorageReferenceMapper
{
    protected const CLASSNAME = File::class;
    protected const FORM_FIELD = 'file';

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly array $channels
    ) {
    }

    protected function newFile(StorageInterface $storage): File
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
