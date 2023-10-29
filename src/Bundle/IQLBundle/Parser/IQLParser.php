<?php

namespace Integrated\Bundle\IQLBundle\Parser;

use Integrated\Bundle\IQLBundle\Specification\PublishedAfter;
use Integrated\Bundle\IQLBundle\Specification\PublishedBefore;
use Integrated\Bundle\IQLBundle\Specification\PublishedOn;
use Integrated\Bundle\IQLBundle\Specification\WithContentType;
use Integrated\Bundle\IQLBundle\Specification\WrittenAfter;
use Integrated\Bundle\IQLBundle\Specification\WrittenBefore;
use Integrated\Bundle\IQLBundle\Specification\WrittenBy;
use Integrated\Bundle\IQLBundle\Specification\WrittenOn;
use Stratadox\Parser\Parser;
use Stratadox\Parser\Parsers\Either;
use Stratadox\Parser\Parsers\End;
use function Stratadox\Parser\any;
use function Stratadox\Parser\text;

final class IQLParser
{
    public static function create(): Parser
    {
        return Either::of(
            text('written by ')->ignore()
                ->andThen(any()->repeatableString()->map(fn (string $who) => WrittenBy::author($who))),

            text('written on ')->ignore()
                ->andThen(any()->repeatableString()->map(fn (string $when) => WrittenOn::date($when))),
            text('written before ')->ignore()
                ->andThen(any()->repeatableString()->map(fn (string $when) => WrittenBefore::date($when))),
            text('written after ')->ignore()
                ->andThen(any()->repeatableString()->map(fn (string $when) => WrittenAfter::date($when))),

            text('published on ')->ignore()
                ->andThen(any()->repeatableString()->map(fn (string $when) => PublishedOn::date($when))),
            text('published before ')->ignore()
                ->andThen(any()->repeatableString()->map(fn (string $when) => PublishedBefore::date($when))),
            text('published after ')->ignore()
                ->andThen(any()->repeatableString()->map(fn (string $when) => PublishedAfter::date($when))),

            any()->except(End::with(text('s')))->repeatableString()
                ->andThen('s')->map(fn ($a) => [WithContentType::of($a[0])])
        )->map(fn ($a) => $a[0]);
    }
}
