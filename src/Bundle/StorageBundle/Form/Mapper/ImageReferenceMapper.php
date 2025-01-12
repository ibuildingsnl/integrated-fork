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

class ImageReferenceMapper extends StorageReferenceMapper
{
    protected const CLASSNAME = Image::class;
    protected const FORM_FIELD = 'image';

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly array $channels,
    ) {
    }

    protected function newFile(StorageInterface $storage): Image
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
