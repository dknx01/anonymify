<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Obfuscate\Task;

use App\Configuration\Obfuscate\Definition;
use App\Configuration\Obfuscate\TableDefinitions;
use Doctrine\DBAL\Exception;
use Monolog\Attribute\WithMonologChannel;

#[WithMonologChannel('obfuscate')]
final class Bic extends ObfuscateAbstract implements ObfuscateTask
{
    public static function getName(): string
    {
        return 'bic';
    }

    /**
     * @throws Exception
     */
    public function run(Definition $definition, TableDefinitions $tableDefinitions): void
    {
        $this->logger->info('BIC generation');
        $this->logger->debug(sprintf('Column %s', $definition->column));

        foreach ($this->getTablesWithColumns($definition->column) as $table) {
            $this->logger->debug(sprintf('Table %s', $table));
            if ($tableDefinitions->hasColumnDefinition($table, $definition->column)) {
                continue;
            }
            $this->processTable($table, $definition);
        }
    }

    /**
     * @throws Exception
     */
    public function runForTable(Definition $definition, string $table): void
    {
        $this->logger->info('BIC generation');
        $this->logger->debug(sprintf('Column %s', $definition->column));
        $this->logger->debug(sprintf('Table %s', $table));
        $this->processTable($table, $definition);
    }

    protected function processTable(string $table, Definition $definition): void
    {
        $this->generateTemporarytable($table);
        foreach ($this->getRows($table) as $row) {
            if (!$this->isColumnBlank($row[$definition->column] ?? null)) {
                $row[$definition->column] = $this->faker->unique()->bankAccountNumber();
            }
            $this->insertData($row);
            unset($row);
        }
        $this->copyDataFromTemporaryToRealTable($table);
    }
}
