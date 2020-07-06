<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\StaticContent;

class StaticContentRepository
{
    private $content = [];

    /**
     * @param string $file
     *
     * @throws \Exception
     */
    public function registerFile(string $file)
    {
        $items = simplexml_load_file($file);

        foreach ($items->item as $element) {
            if (!$element->id) {
                throw new \Exception(sprintf('id is required in %s', $file));
            }

            if (!$element->class) {
                throw new \Exception(sprintf('class is required in %s', $file));
            }

            if (!class_exists($element->class)) {
                throw new \Exception(sprintf('Class %s does not existsin %s', $element->class, $file));
            }

            $key = str_replace('\\', '_', $element->class);
            $this->content[$key][(string) $element->id] = json_decode(json_encode($element->fields), true);
        }
    }

    /**
     * @param object|null $object
     *
     * @return mixed|null
     */
    public function get(?object $object)
    {
        if ($object === null) {
            return null;
        }

        $key = str_replace('\\', '_', get_class($object));
        if (!isset($this->content[$key])) {
            return null;
        }

        $id = $object->getId();

        return $this->content[$key][$id] ?? null;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->content;
    }
}
