<?php

declare(strict_types=1);

namespace PetrKnap\FilterCommand;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class FilterCommand
{
    /**
     * @param non-empty-string $command
     * @param array<non-empty-string> $options
     */
    public function __construct(
        private readonly string $command,
        private readonly array $options = [],
    ) {
    }

    /**
     * @throws Exception\CouldNotFilterData
     */
    public function filter(string $data): string
    {
        $process = new Process([
            $this->command,
            ...$this->options,
        ]);
        $process->setInput($data);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new class (__METHOD__, $data, new ProcessFailedException($process)) extends Exception\CouldNotFilterData {
            };
        }
        return $process->getOutput();
    }
}
