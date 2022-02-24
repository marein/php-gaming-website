<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Channel\Channel;

final class Processes
{
    /**
     * @var Process[]
     */
    private array $processes;

    public function __construct()
    {
        $this->processes = [];
    }

    public function add(int $processId, Channel $channel): Process
    {
        $process = new Process($processId, $channel);

        $this->processes[] = $process;

        return $process;
    }

    public function kill(int $signal): void
    {
        foreach ($this->processes as $process) {
            $process->kill($signal);
        }
    }
}
