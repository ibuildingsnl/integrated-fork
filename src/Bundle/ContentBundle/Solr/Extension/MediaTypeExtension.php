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
class MediaTypeExtension implements TypeExtensionInterface
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
     * It sets the media_type_string in Solr. This key can hold multiple values.
     * It always adds: ContentType, so we can search for this
     * It always adds: File, so we can search for all Files
     * Then Based on the ContentType, it adds one more, based on what it is.
     *  - Video -> Video
     *  - Image -> Image
     *  - File -> OtherFile (So we can distinguish between File and OtherFile)
     *  - Other -> Other (Can be anything)
     */
    public function buildClassStringExtension(ContainerInterface $container, $data, array $options = [])
    {
        if (!$data instanceof File) {
            return;
        }

        $container->remove('media_type_string');

        $this->addValueToKey($container, 'media_type_string', 'File');

        $contentType = $data->getRelations()->getOwner()->getContentType();

        if ($data instanceof Image) {
            $this->addValueToKey($container, 'media_type_string', 'Image');
        } elseif ($data instanceof Video) {
            $this->addValueToKey($container, 'media_type_string', 'Video');
        } else {
            //We cant use instanceof File to improve this code,
            //Since all contentTypes have something like /File/Video or File/Image
            if ($contentType !== 'video' &&
                $contentType !== 'image' &&
                $contentType !== 'file') {
                $this->addValueToKey($container, 'media_type_string', $contentType);
            } else {
                $this->addValueToKey($container, 'media_type_string', 'OtherFile');
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
