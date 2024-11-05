<?php

declare(strict_types=1);

namespace PetrKnap\FilterCommand;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FilterCommandTest extends TestCase
{
    public function testFiltersInputByCommand(): void
    {
        self::assertSame(
            'test',
            (new FilterCommand('php'))->filter('<?php echo "test";'),
        );
    }

    #[DataProvider('dataThrows')]
    public function testThrows(string $command, array $options, string $data): void
    {
        self::expectException(Exception\CouldNotFilterData::class);

        (new FilterCommand($command, $options))->filter($data);
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
