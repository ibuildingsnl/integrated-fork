<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search\Type;

interface TypeProviderInterface
{
    public function hasType(string $name): bool;

    public function getType(string $name): TypeInterface;

    public function hasExtensions(string $name): bool;

    /**
     * @return TypeExtensionInterface[]
     */
    public function getExtensions(string $name): iterable;
}
