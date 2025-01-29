<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Configuration\Obfuscate;

use App\Configuration\Anonymify\Table;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    public static function provideTableData(): \Generator
    {
        yield ['tableName', []];
        $columns = 'ip';
        yield ['tableName', ['mc_sync_double_opt_in' => $columns]];
        $columns = new \stdClass();
        $columns->args = [1, 2];
        $columns->type = 'numbers';
        yield ['tableName', ['mc_sync_double_opt_in' => $columns]];
        yield ['tableName', ['mc_sync_double_opt_in' => null]];
    }

    /**
     * @param array<string, string|\stdClass|null> $columns
     */
    #[DataProvider('provideTableData')]
    public function testTable(string $name, array $columns): void
    {
        $table = new Table($name, $columns);

        $this->assertSame($name, $table->name);
        $this->assertCount(count($columns), $table->columns);
    }
}
