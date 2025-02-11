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

use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;

/**
 * Embedded document Job.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class Job
{
    protected ?string $function = null;

    protected ?string $department = null;

    protected ?Company $company = null;

    public function getFunction(): ?string
    {
        return $this->function;
    }

    public function setFunction(?string $function): self
    {
        $this->function = $function;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company)
    {
        $this->company = $company;

        return $this;
    }
}
