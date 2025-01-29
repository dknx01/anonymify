<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Anonymify\Column\Task;

use App\Configuration\Anonymify\Definition;
use App\Configuration\Anonymify\TableDefinitions;
use Monolog\Attribute\WithMonologChannel;

#[WithMonologChannel('anonymize')]
final class NoMask extends AnonymifyAbstract implements AnonymifyTask
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
