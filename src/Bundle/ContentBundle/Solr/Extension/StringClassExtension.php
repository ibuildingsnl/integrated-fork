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
//use Integrated\Bundle\ContentBundle\Document\Content\File; not needed,
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Video;
use Integrated\Common\Content\Document\Storage\FileInterface;
/**
 * @author Wouter Koppers
 */
class StringClassExtension implements TypeExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerInterface $container, $data, array $options = [])
    {
        //TODO *1
        //get custom class from data and add this.

        //File of FileInterface?
        if (!$data instanceof FileInterface) {
            return;
        }

        //how is set different than add?
        //why doesnt this remove the key with the console command?
        //can we set multiple fields at once?

        $container->remove('class_string');

        //We always add these
        $container->set('class_string', 'ContentType');
        $container->add('class_string', 'File');

        if ($data instanceof Image) {
            $container->add('class_string', 'Image');
        } else if ($data instanceof Video) {
            $container->add('class_string', 'Video');
        } else {
            $container->add('class_string', 'NonMedia');
        }

        //TODO *1
        //If $data is custom class
        //$container->add('class_string', $customclass);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated.content';
    }
}
