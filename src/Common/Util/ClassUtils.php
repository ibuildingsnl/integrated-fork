<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Integrated\Common\Util;

use Doctrine\Persistence\Proxy;

use function get_class;
use function strrpos;
use function substr;

/**
 * Resolves the real class name of objects that may be Doctrine proxies.
 *
 * This is a drop-in replacement for the identically-named helper in
 * doctrine/common, which is no longer installable alongside Doctrine ORM 3.
 * The implementation is deliberately identical so proxy resolution keeps
 * behaving exactly as before.
 */
final class ClassUtils
{
    public static function getRealClass(string $className): string
    {
        $pos = strrpos($className, '\\' . Proxy::MARKER . '\\');

        if ($pos === false) {
            return $className;
        }

        return substr($className, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * @psalm-param Proxy<T>|T $object
     *
     * @psalm-return class-string<T>
     *
     * @template T of object
     */
    public static function getClass(object $object): string
    {
        return self::getRealClass(get_class($object));
    }
}
