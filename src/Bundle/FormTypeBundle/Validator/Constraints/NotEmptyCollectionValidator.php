<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\FormTypeBundle\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\UserBundle\Validator\ManagerConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class NotEmptyCollectionValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof ManagerConstraint) {
            throw new UnexpectedTypeException($constraint, NotEmptyCollection::class);
        }

        if (null === $value) {
            $this->context->buildViolation($constraint->message)->addViolation();
        } elseif ($value instanceof Collection) {
            if (!$value->count()) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
