<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 *
 * @Annotation
 */
abstract class ManagerConstraint extends Constraint
{
    public $message;
    public $manger;
    public $method = 'findBy';
    public $fields = [];

    public function getRequiredOptions(): array
    {
        return ['manger'];
    }

    public function getDefaultOption(): ?string
    {
        return 'manger';
    }

    public function getTargets(): string|array
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
