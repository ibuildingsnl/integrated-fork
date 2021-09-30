<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\FormTypeBundle\Form\DataTransformer;

use Darsyn\IP\Exception\IpException;
use Darsyn\IP\Version\Multi;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IpAddressTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (null === $value) {
            return null;
        } elseif ($value instanceof Multi) {
            return $value->getProtocolAppropriateAddress();
        }

        throw new TransformationFailedException(sprintf('Expected %s, "%s" given', Multi::class, \gettype($value)));
    }

    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        } elseif (\is_string($value)) {
            try {
                return Multi::factory($value);
            } catch (IpException $ipException) {
                throw new TransformationFailedException($ipException->getMessage(), 0, $ipException);
            }
        }

        throw new TransformationFailedException(sprintf('Expected string, "%s" given', \gettype($value)));
    }
}
