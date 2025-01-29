<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Configuration\Obfuscate;

use App\Configuration\Anonymify\Table;
use App\Configuration\Anonymify\TableDefinitions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TableDefinitionsTest extends TestCase
{
    public static function provideTableDefinitions(): \Generator
    {
        yield [
            false,
            [],
            ['table' => 'foo', 'column' => 'bar'],
        ];
        yield [
            false,
            [
                'mc_sync_double_opt_in' => new Table('mc_sync_double_opt_in', ['ip' => 'ip']),
            ],
            ['table' => 'foo', 'column' => 'bar'],
        ];
        yield [
            true,
            [
                'mc_sync_double_opt_in' => new Table('mc_sync_double_opt_in', ['ip' => 'ip']),
            ],
            ['table' => 'mc_sync_double_opt_in', 'column' => 'ip'],
        ];
    }

    /**
     * @param array<string, Table>  $tables
     * @param array<string, string> $callArgs
     */
    #[DataProvider('provideTableDefinitions')]
    public function testHasColumnDefinition(bool $expected, array $tables, array $callArgs): void
    {
        $tableDefinitions = new TableDefinitions($tables);
        $this->assertSame($expected, $tableDefinitions->hasColumnDefinition(...$callArgs));
    }
}
