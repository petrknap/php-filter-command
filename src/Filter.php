<?php

declare(strict_types=1);

namespace PetrKnap\ExternalFilter;

use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Filter
{
    private self|null $previous = null;

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
     * @param string|resource $input
     *
     * @throws Exception\FilterException
     */
    public function filter(mixed $input): string
    {
        if (!is_string($input) && !is_resource($input)) {
            throw new class ('$input must be string|resource') extends InvalidArgumentException implements Exception\FilterException {
            };
        }

        $process = $this->startFilter($input);

        $process->wait();
        if (!$process->isSuccessful()) {
            throw new class ($process) extends ProcessFailedException implements Exception\FilterException {
            };
        }

        return $process->getOutput();
    }

    public function pipe(self $to): self
    {
        $reversedPipeline = [];
        $head = $to;
        while ($head !== null) {
            $reversedPipeline[] = $head;
            $head = $head->previous;
        }

        $base = $this;
        foreach (array_reverse($reversedPipeline) as $next) {
            $next = new self($next->command, $next->options);
            $next->previous = $base;
            $base = $next;
        }

        return $base;
    }

    /**
     * @param string|resource $input
     */
    private function startFilter(mixed $input): Process
    {
        if ($this->previous !== null) {
            $input = $this->previous->startFilter($input);
        }

        $process = new Process([
            $this->command,
            ...$this->options,
        ]);
        $process->setInput($input);
        $process->start();

        return $process;
    }
}
