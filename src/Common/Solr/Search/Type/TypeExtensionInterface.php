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

use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface TypeExtensionInterface
{
    public function build(Query $query, array $options): void;

    public function configureOptions(OptionsResolver $resolver): void;

    /**
     * @return string[]
     */
    public static function getTypes(): iterable;
}
