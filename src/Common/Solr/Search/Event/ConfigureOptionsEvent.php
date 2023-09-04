<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search\Event;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\Event;

class ConfigureOptionsEvent extends Event
{
    public function __construct(
        private readonly string $formType,
        private readonly OptionsResolver $resolver,
    ) {
    }

    public function getFormType(): string
    {
        return $this->formType;
    }

    public function getResolver(): OptionsResolver
    {
        return $this->resolver;
    }
}
