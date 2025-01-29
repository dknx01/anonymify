<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Configuration\Obfuscate;

use App\Configuration\Anonymify\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public static function provideConfig(): \Generator
    {
        yield [
            ['general' => [], 'tables' => []],
        ];

        yield [
            [
                'general' => [
                    'cName' => null,
                    'nIP' => 'ip',
                ],
                'tables' => [],
            ],
        ];

        yield [
            [
                'general' => [
                    'cName' => null,
                    'nIP' => 'ip',
                ],
                'tables' => [
                    'mc_sync_double_opt_in' => ['ip' => 'ip'],
                ],
            ],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $configData
     */
    #[DataProvider('provideConfig')]
    public function testFromArray(array $configData): void
    {
        $config = Config::fromArray($configData);
        $this->assertCount(count($configData['general']), $config->general);
        $this->assertCount(count($configData['tables']), $config->tables);
    }
}
