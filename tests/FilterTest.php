<?php

declare(strict_types=1);

namespace PetrKnap\ExternalFilter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FilterTest extends TestCase
{
    #[DataProvider('dataFiltersInput')]
    public function testFiltersInput($input, string $expectedOutput): void
    {
        self::assertSame(
            $expectedOutput,
            (new Filter('php'))->filter($input),
        );
    }

    public static function dataFiltersInput(): iterable
    {
        $helloWorldPhpStdOut = 'Hello, World!';
        $helloWorldPhpFile = __DIR__ . '/Some/hello-world.php';

        $fileContent = file_get_contents($helloWorldPhpFile);
        yield 'string(file content)' => [$fileContent, $helloWorldPhpStdOut];

        $filePointer = fopen($helloWorldPhpFile, 'r');
        yield 'resource(file pointer)' => [$filePointer, $helloWorldPhpStdOut];

        $inMemoryStream = fopen('php://memory', 'w+');
        fwrite($inMemoryStream, $fileContent);
        rewind($inMemoryStream);
        yield 'resource(in-memory stream)' => [$inMemoryStream, $helloWorldPhpStdOut];
    }

    public function testBuildsAndExecutesPipeline(): void
    {
        $pipeline = (new Filter('gzip'))->pipe(new Filter('base64'))->pipe(new Filter('base64', ['--decode']));
        $filter = new Filter('gzip', ['--decompress']);

        self::assertSame(
            'test',
            (new Filter('cat'))->pipe($pipeline)->pipe($filter)->filter('test'),
        );
    }

    #[DataProvider('dataThrows')]
    public function testThrows(string $command, array $options, mixed $input): void
    {
        self::expectException(Exception\FilterException::class);

        (new Filter($command, $options))->filter($input);
    }

    public static function dataThrows(): array
    {
        return [
            'unknown command' => ['unknown', [], ''],
            'unknown option' => ['php', ['--unknown'], ''],
            'wrong data' => ['php', [], '<?php wrong data'],
            'wrong input' => ['php', [], new stdClass()],
        ];
    }
}
