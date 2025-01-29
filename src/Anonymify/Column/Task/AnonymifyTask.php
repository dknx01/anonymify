<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Anonymify\Column\Task;

use App\Configuration\Anonymify\Definition;
use App\Configuration\Anonymify\TableDefinitions;

interface AnonymifyTask
{
    public static function getName(): string;

    public function run(Definition $definition, TableDefinitions $tableDefinitions): void;

    public function runForTable(Definition $definition, string $table): void;
}
