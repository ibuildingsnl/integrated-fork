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
use Stratadox\Specification\Contract\Specifies;
use function Stratadox\Parser\any;
use function Stratadox\Parser\text;

final class IQLParser
{
    public static function create(): Parser
    {
        return self::contentType(' items', 's')->or(self::rules())
            ->andThen(self::delimiter()->optional()->andThen(self::rules())->first()->repeatable())
            ->map(function (array $result) {
                $specification = ($result[0] ?? null);
                if (!$specification instanceof Specifies) {
                    return null;
                }
                if (!isset($result[1])) {
                    return $specification;
                }
                if ($result[1] instanceof Specifies) {
                    return $specification->and($result[1]);
                }
                foreach ($result[1] as $also) {
                    $specification = $specification->and($also);
                }
                return $specification;
            });
    }

    private static function contentType(Parser|string ...$suffix): Parser
    {
        $delimiter = Either::of(...$suffix);
        return any()->except($delimiter->end()->or($delimiter->andThen(' ')))->repeatableString()
            ->map(fn (string $type) => WithContentType::of($type))
            ->andThen($delimiter)
            ->first();
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
        )->first();
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
        return text(', ')->or(' or ', ' and ')->ignore();
    }

    private static function quotedText(): Parser
    {
        return Between::escaped('"', '"', '\\');
    }
}
