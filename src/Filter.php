<?php

declare(strict_types=1);

namespace PetrKnap\ExternalFilter;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Filter
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
     * @throws Exception\FilterException
     */
    public function filter(string $input): string
    {
        $process = new Process([
            $this->command,
            ...$this->options,
        ]);
        $process->setInput($input);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new class ($process) extends ProcessFailedException implements Exception\FilterException {
            };
        }
        return $process->getOutput();
    }
}
