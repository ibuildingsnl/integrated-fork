<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

/**
 * Embedded document Phonenumber.
 *
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Phonenumber
{
    protected string $number;

    protected ?string $type;

    public function __construct(string $number, ?string $type = null)
    {
        $this->number = $number;
        $this->type = $type;
    }

    public function setNumber(string $number)
    {
        $this->number = $number;

        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}
