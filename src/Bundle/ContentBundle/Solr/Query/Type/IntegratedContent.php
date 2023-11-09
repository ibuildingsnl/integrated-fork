<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Solr\Query\Type;

use Integrated\Common\Solr\Search\Type\AbstractType;

class IntegratedContent extends AbstractType
{
    public function getParent(): ?string
    {
        return Content::class;
    }
}
