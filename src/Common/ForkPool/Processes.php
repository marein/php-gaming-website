<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool;

use Gaming\Common\ForkPool\Channel\Channel;

final class Processes
{
    /**
     * @var array<int, Process>
     */
    private array $processes;

    public function __construct()
    {
        $this->processes = [];
    }

    public function add(int $processId, Channel $channel): Process
    {
        $process = new Process($processId, $channel);

        $this->processes[$processId] = $process;

        return $process;
    }

    public function remove(int $processId): void
    {
        if (array_key_exists($processId, $this->processes)) {
            unset($this->processes[$processId]);
        }
    }

    public function kill(int $signal): void
    {
        foreach ($this->processes as $process) {
            $process->kill($signal);
        }
    }
}
