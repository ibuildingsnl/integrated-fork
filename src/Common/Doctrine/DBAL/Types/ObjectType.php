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

namespace Integrated\Common\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

use function is_resource;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function sprintf;
use function stream_get_contents;
use function unserialize;

/**
 * Maps a PHP object to a CLOB column using PHP serialization.
 *
 * DBAL 4 removed its built-in `object` type. This is a behaviour-identical
 * reimplementation, kept so existing serialized column data stays readable —
 * switching the mapping to `json` instead would require migrating every stored
 * value. Register it as the `object` type; see the host application's
 * `doctrine.dbal.types` configuration.
 */
final class ObjectType extends Type
{
    public const NAME = 'object';

    /** @param mixed[] $column */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        return serialize($value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        set_error_handler(static function (int $code, string $message): bool {
            throw new ConversionException(sprintf('Could not convert database value to "%s" as an error was triggered by the unserialization: "%s".', self::NAME, $message));
        });

        try {
            return unserialize((string) $value);
        } finally {
            restore_error_handler();
        }
    }
}
