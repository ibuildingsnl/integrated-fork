<?php

namespace Integrated\Bundle\ImportBundle\Import\Converter;

use Symfony\Component\HttpFoundation\Session\Session;

class ExecuteImporter
{
    public static function configureExecutionEnvironment() {
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '4G');
    }

    public static function handleSession() {
        $session = new Session();
        $session->save();
    }

    public static function getData($importDefinition, $doctrine, $importFile) {
        if ($importDefinition->getConnectionUrl() && $importDefinition->getConnectionQuery()) {
            return $doctrine->toArray($importDefinition);
        } else {
            return $importFile->toArray($importDefinition);
        }
    }

    public static function initializeResult() {
        return [
            'done' => true,
            'success' => [],
            'warnings' => [],
            'updates' => [],
            'errors' => [],
        ];
    }

    public static function calculateRemainingTime($request, $newStart, $totalRowNumber)
    {
        $startTime = (int)$request->get('startTime', time());
        $duration = time() - $startTime;
        $remaining = ($duration / ($newStart - 1)) * ($totalRowNumber - ($newStart - 1));

        if ($remaining >= 120) {
            $remaining = round($remaining / 60) . ' minutes';
        } else {
            $remaining = round($remaining) . ' seconds';
        }

        if (($newStart - 1) >= $totalRowNumber) {
            $percentage = 100;
            $remainingStr = '';
        } else {
            $startRow = $newStart;
            $percentage = round((($newStart - 1) / $totalRowNumber) * 100);
            $remainingStr = 'Estimated remaining time: ' . $remaining;
        }

        return [
            'percentage' => $percentage,
            'remaining' => $remainingStr,
            'startRow' => $startRow ?? null,  // This will return null if $startRow is not set
        ];
    }
}
