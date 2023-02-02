<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Document type Image.
 *
 * @author Johnny Borg <johnny@e-active.nl>
 */
#[Type\Document('Image')]
class Image extends File
{
    /**
     * @var StorageInterface
     */
    #[Assert\File(mimeTypes: ['image/*', 'application/postscript'])]
    #[Type\Field(type: 'Integrated\Bundle\StorageBundle\Form\Type\FileDropzoneType', options: [
        'priority' => 500,
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    protected $file;
}
