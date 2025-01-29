<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Obfuscate\Task;

use App\Configuration\Obfuscate\Definition;
use App\Configuration\Obfuscate\TableDefinitions;
use Monolog\Attribute\WithMonologChannel;

#[WithMonologChannel('obfuscate')]
final class NoMask extends ObfuscateAbstract implements ObfuscateTask
{
    public static function getName(): string
    {
        return 'nomask';
    }

    public function run(Definition $definition, TableDefinitions $tableDefinitions): void
    {
        $this->logger->info('NoMask generation');
        // nothing to change
    }

    public function runForTable(Definition $definition, string $table): void
    {
        $this->logger->info('NoMask generation');
        // nothing to change
    }

    protected function processTable(string $table, Definition $definition): void
    {
        // nothing to do
    }
}
