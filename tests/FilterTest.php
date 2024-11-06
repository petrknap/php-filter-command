<?php

declare(strict_types=1);

namespace PetrKnap\ExternalFilter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    public function testFiltersInput(): void
    {
        self::assertSame(
            'test',
            (new Filter('php'))->filter('<?php echo "test";'),
        );
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
    public function testThrows(string $command, array $options, string $input): void
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
        ];
    }
}
