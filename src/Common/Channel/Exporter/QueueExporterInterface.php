<?php

namespace Integrated\Common\Channel\Exporter;

interface QueueExporterInterface
{
    public function exportMessages(int $limit = 1000): int;

    public function hasMessages(): bool;
}
