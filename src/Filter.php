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

        $process = Process::fromShellCommandline($this->buildShellCommandLine());
        $process->setInput($input);
        $process->run(static function (string $type, string $data) use ($output, $error): void {
            /** @var Process::OUT|Process::ERR $type */
            match ($type) {
                Process::OUT => $output === null or fwrite($output, $data),
                Process::ERR => $error === null or fwrite($error, $data),
            };
        });
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

    private function buildShellCommandLine(): string
    {
        return ($this->previous === null ? '' : "{$this->previous->buildShellCommandLine()} | ")
            . (new Process([
                $this->command,
                ...$this->options,
            ]))->getCommandLine();
    }
}
