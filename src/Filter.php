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
     * @param resource|null $output
     * @param resource|null $error
     *
     * @return ($output is null ? string : null)
     *
     * @throws Exception\FilterException
     */
    public function filter(mixed $input, mixed $output = null, mixed $error = null): string|null
    {
        if (!is_string($input) && !is_resource($input)) {
            throw new class ('$input must be string|resource') extends InvalidArgumentException implements Exception\FilterException {
            };
        }
        if ($output !== null && !is_resource($output)) {
            throw new class ('$output must be resource|null') extends InvalidArgumentException implements Exception\FilterException {
            };
        }
        if ($error !== null && !is_resource($error)) {
            throw new class ('$error must be resource|null') extends InvalidArgumentException implements Exception\FilterException {
            };
        }

        $process = $this->startFilter($input, static function (string $type, string $data) use ($output, $error): void {
            /** @var Process::OUT|Process::ERR $type */
            match ($type) {
                Process::OUT => $output === null or fwrite($output, $data),
                Process::ERR => $error === null or fwrite($error, $data),
            };
        });
        $process->wait();
        if (!$process->isSuccessful()) {
            throw new class ($process) extends ProcessFailedException implements Exception\FilterException {
            };
        }
        return $output === null ? $process->getOutput() : null;
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
    private function startFilter(mixed $input, callable|null $callback): Process
    {
        if ($this->previous !== null) {
            $input = $this->previous->startFilter($input, null);
        }

        $process = new Process([
            $this->command,
            ...$this->options,
        ]);
        $process->setInput($input);
        $process->start($callback);

        return $process;
    }
}
