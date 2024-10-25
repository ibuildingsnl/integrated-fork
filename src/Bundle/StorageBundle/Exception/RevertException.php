<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Exception;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class RevertException extends \ErrorException
{
    /**
     * @param string $filesystem
     * @param string $identifier
     *
     * @return self
     */
    public static function writeFailed($filesystem, $identifier)
    {
        return new self(
            \sprintf(
                'The filesystem %s denied writing for key %s',
                $filesystem,
                $identifier
            )
        );
    }
}
