<?php

namespace Integrated\Bundle\IQLBundle\Parser;

use Stratadox\Parser\Parser;
use function Stratadox\Parser\text;

final class IQLParser
{
    public static function create(): Parser
    {
        return text('foo');
    }
}
