<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Obfuscate\Task;

use App\Configuration\Obfuscate\Definition;
use App\Configuration\Obfuscate\TableDefinitions;

interface ObfuscateTask
{
    public static function getName(): string;

    public function run(Definition $definition, TableDefinitions $tableDefinitions): void;

    public function runForTable(Definition $definition, string $table): void;
}
