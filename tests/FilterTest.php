<?php

declare(strict_types=1);

namespace PetrKnap\ExternalFilter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    public function testFilters(): void
    {
        self::assertSame(
            'test',
            (new Filter('php'))->filter('<?php echo "test";'),
        );
    }

    #[DataProvider('dataThrows')]
    public function testThrows(string $command, array $options, string $data): void
    {
        self::expectException(Exception\FilterException::class);

        (new Filter($command, $options))->filter($data);
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
