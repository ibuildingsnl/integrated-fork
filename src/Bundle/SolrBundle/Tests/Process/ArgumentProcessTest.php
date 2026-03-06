<?php

namespace Integrated\Bundle\SolrBundle\Tests\Process;

use Integrated\Bundle\SolrBundle\Process\ArgumentProcess;
use PHPUnit\Framework\TestCase;

class ArgumentProcessTest extends TestCase
{
    public function testSupportsMultiDigitChildProcessFormat(): void
    {
        $argument = new ArgumentProcess('12:34');

        self::assertFalse($argument->isParentProcess());
        self::assertSame(12, $argument->getProcessNumber());
        self::assertSame(34, $argument->getProcessMax());
    }
}
