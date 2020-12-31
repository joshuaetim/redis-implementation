<?php

declare(strict_types=1);

namespace App\MyChecks;

use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;

class MemoryUsage extends CheckDefinition
{
    public $command = '';

    public function resolve(Process $process)
    {
        $percentage = $this->getMemoryUsagePercentage();

        $message = "memory usage at {$percentage}%";

        $thresholds = config('server-monitor.memory_percentage_threshold');

        if($percentage >= $thresholds['fail']){
            $this->check->fail($message);
            return;
        }

        if($percentage >= $thresholds['warn']){
            $this->check->warn($message);
            return;
        }

        $this->check->succeed($message);
    }

    protected function getMemoryUsagePercentage(): int
    {
        $fh = fopen("/proc/meminfo", "r");

        $content = '';

        while($line = fgets($fh)){
            $content .= $line;
        }
        fclose($fh);

        preg_match_all('/(\d+)/', $content, $matches);

        $used = round(($matches[0][6] / $matches[0][0]) * 100, 2);

        return $used;
    }
}