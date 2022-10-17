<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Solr\Extension;

use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;
use Integrated\Bundle\ContentBundle\Document\Content\File; // not needed,
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Video;

/**
 * @author Wouter Koppers
 */
class MultivaluedFieldExtension implements TypeExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerInterface $container, $data, array $options = [])
    {
        $this->buildClassStringExtension($container, $data, $options);
    }

    /*
     * What this does:
     * It sets the class_string in Solr. This key can hold multiple values.
     * It always adds: ContentType, so we can search for this
     * It always adds: File, so we can search for all Files
     * Then Based on the ContentType, it adds one more, based on what it is.
     *  - Video -> Video
     *  - Image -> Image
     *  - File -> NonMedia (So we can distinguish between File and NonMedia)
     *  - Other -> Other (Can be anything)
     */
    public function buildClassStringExtension(ContainerInterface $container, $data, array $options = [])
    {
        if (!$data instanceof File) {
            return;
        }

        $container->remove('class_string');

        $this->addValueToKey($container, 'class_string', 'ContentType');
        $this->addValueToKey($container, 'class_string', 'File');

        if ($data instanceof Image) {
            $this->addValueToKey($container, 'class_string', 'Image');
        } elseif ($data instanceof Video) {
            $this->addValueToKey($container, 'class_string', 'Video');
        } else {
            if ($data->getRelations()->getOwner()->getContentType() !== 'video' &&
                $data->getRelations()->getOwner()->getContentType() !== 'image' &&
                $data->getRelations()->getOwner()->getContentType() !== 'file') {
                $this->addValueToKey($container, 'class_string', $data->getRelations()->getOwner()->getContentType());
            } else {
                $this->addValueToKey($container, 'class_string', 'NonMedia');
            }
        }
    }

    public function addValueToKey($container, $key, $value)
    {
        $container->add($key, $value);
    }

    public function removeKeyAndValue($container, $key)
    {
        $container->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated.content';
    }
}
