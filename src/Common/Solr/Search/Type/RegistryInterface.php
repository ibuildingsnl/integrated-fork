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

interface RegistryInterface
{
    public function hasType(string $name): bool;

    public function getType(string $name): ResolvedTypeInterface;
}
