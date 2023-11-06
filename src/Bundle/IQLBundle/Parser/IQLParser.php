<?php

namespace Integrated\Bundle\IQLBundle\Parser;

use Integrated\Bundle\IQLBundle\Specification\PublishedAfter;
use Integrated\Bundle\IQLBundle\Specification\PublishedBefore;
use Integrated\Bundle\IQLBundle\Specification\PublishedOn;
use Integrated\Bundle\IQLBundle\Specification\PublishedTo;
use Integrated\Bundle\IQLBundle\Specification\WithContent;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenAfter;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Specification\WrittenBy;
use Integrated\Bundle\IQLBundle\Specification\WrittenOn;
use Stratadox\Parser\Helpers\Between;
use Stratadox\Parser\Helpers\Cast;
use Stratadox\Parser\Parser;
use Stratadox\Parser\Parsers\Either;
use function Stratadox\Parser\any;
use function Stratadox\Parser\text;

final class IQLParser
{
    public static function create(): Parser
    {
        $d = text(' items')->or('s');
        return any()->except($d->end())->repeatableString()->map(fn (string $type) => WithContentType::of($type))->andThen($d)
            ->or(self::rules())->map(fn ($a) => $a[0]);
    }

    private static function rules(): Parser
    {
        return Either::of(
            self::rule('written by ', fn (string $who) => WrittenBy::author($who)),
            self::rule('written on ', fn (string $when) => WrittenOn::date($when)),
            self::rule('written before ', fn (string $when) => WrittenBefore::date($when)),
            self::rule('written after ', fn (string $when) => WrittenAfter::date($when)),
            self::rule('published on ', fn (string $when) => PublishedOn::date($when)),
            self::rule('published before ', fn (string $when) => PublishedBefore::date($when)),
            self::rule('published after ', fn (string $when) => PublishedAfter::date($when)),
            self::rule('published to ', fn (string $where) => PublishedTo::channel($where)),
            self::rule('containing ', fn (string $text) => WithContent::containing($text)),
            self::between(
                'written between ',
                fn (array $when) => [WrittenAfter::date($when[0])->and(WrittenBefore::date($when[1]))]
            ),
            self::between(
                'published between ',
                fn (array $when) => [PublishedAfter::date($when[0])->and(PublishedBefore::date($when[1]))]
            ),
        );
    }

    private static function rule(Parser|string $prefix, \Closure $mapping): Parser
    {
        return Cast::asParser($prefix)->ignore()->andThen(
            self::quotedText()->or(any()->except(self::delimiter())->repeatableString())->map($mapping)
        );
    }

    private static function between(Parser|string $prefix, \Closure $mapping): Parser
    {
        return Cast::asParser($prefix)->ignore()
            ->andThen(self::quotedText()->or(any()->except(' and ')->repeatableString()))
            ->andThen(text(' and ')->ignore())
            ->andThen(self::quotedText()->or(any()->except(self::delimiter())->repeatableString()))
            ->map($mapping);
    }

    private static function delimiter(): Parser
    {
        return text(', ')->or(' or', ' and');
    }

    private static function quotedText(): Parser
    {
        return Between::escaped('"', '"', '\\');
    }
}
