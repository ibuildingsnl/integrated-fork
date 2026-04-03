<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Integrated\Bundle\ContentBundle\Services\ChannelDeletionReport;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionWarning;
use PHPUnit\Framework\TestCase;

final class ChannelDeletionProcessorTest extends TestCase
{
    public function testSuccessWithWarnings(): void
    {
        $report = new ChannelDeletionReport('beveragenl');
        $report->markRemovedChannel();
        $report->addWarning(new ChannelDeletionWarning('delete', 'Integrated\\Bundle\\ContentBundle\\Document\\Content', 'abc', 'boom'));

        self::assertSame('success_with_warnings', $report->getStatus());
        self::assertSame(1, $report->getWarningCount());
    }
}
