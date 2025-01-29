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
use Random\RandomException;

#[WithMonologChannel('obfuscate')]
final class Kontonummer extends ObfuscateAbstract implements ObfuscateTask
{
    public static function getName(): string
    {
        return 'kontonr';
    }

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function run(Definition $definition, TableDefinitions $tableDefinitions): void
    {
        $this->logger->info('Kontonummer generation');
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
     * @throws RandomException
     * @throws Exception
     */
    public function runForTable(Definition $definition, string $table): void
    {
        $this->logger->info('Kontonummer generation');
        $this->logger->debug(sprintf('Column %s', $definition->column));

        $this->processTable($table, $definition);
    }

    /**
     * @throws Exception
     * @throws RandomException
     */
    protected function processTable(string $table, Definition $definition): void
    {
        $this->generateTemporaryTable($table);
        foreach ($this->getRows($table) as $row) {
            if (!$this->isColumnBlank($row[$definition->column] ?? null)) {
                $row[$definition->column] = random_int(1000000000, 9999999999);
            }
            $this->insertData($row);
            unset($row);
        }
        $this->copyDataFromTemporaryToRealTable($table);
    }
}
