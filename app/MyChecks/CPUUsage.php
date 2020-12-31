<?php

declare(strict_types=1);

namespace App\MyChecks;

use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;

class CPUUsage extends CheckDefinition
{
    public $command = '';

    public function resolve(Process $process)
    {
        $percentage = $this->getCPUUsagePercentage();
        $usage = round($percentage, 2);

        $message = "usage at {$usage}%";
        $thresholds = config('server-monitor.cpu_percentage_threshold');

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

    protected function getCPUUsagePercentage(): float
    {
        $cpu = shell_exec("grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage}'");

        return (float) $cpu;
    }
}