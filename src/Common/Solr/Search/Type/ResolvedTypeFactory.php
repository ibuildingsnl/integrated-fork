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

class ResolvedTypeFactory implements ResolvedTypeFactoryInterface
{
    public function create(TypeInterface $type, array $extensions, ?ResolvedTypeInterface $parent = null): ResolvedTypeInterface
    {
        return new ResolvedType($type, $extensions, $parent);
    }
}
