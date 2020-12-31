<?php

declare(strict_types=1);

namespace App\MyChecks;

use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;

class Apache extends CheckDefinition
{
    public $command = 'systemctl is-active apache2';

    public function resolve(Process $process)
    {
        if(str_contains($process->getOutput(), 'active')){
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');

        
    }
}