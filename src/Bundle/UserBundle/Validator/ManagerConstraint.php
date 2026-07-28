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
 */
abstract class ManagerConstraint extends Constraint
{
    public $message = null;
    public $manger = null;
    public $method = 'findBy';
    public $fields = [];

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['manger'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): string|null
    {
        return 'manger';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): array|string
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
