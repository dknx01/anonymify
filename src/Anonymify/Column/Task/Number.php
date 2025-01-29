<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Anonymify\Column\Task;

use App\Configuration\Anonymify\Definition;
use App\Configuration\Anonymify\TableDefinitions;
use Doctrine\DBAL\Exception;
use Monolog\Attribute\WithMonologChannel;
use Random\RandomException;

#[WithMonologChannel('anonymize')]
final class Number extends AnonymifyAbstract implements AnonymifyTask
{
    public static function getName(): string
    {
        return 'numbers';
    }

    /**
     * @throws Exception
     * @throws RandomException
     */
    public function run(Definition $definition, TableDefinitions $tableDefinitions): void
    {
        $this->logger->info('Numbers generation');
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
        $this->logger->info('Numbers generation');
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
                $row[$definition->column] = random_int(...$definition->parameters);
            }
            $this->insertData($row);
            unset($row);
        }
        $this->copyDataFromTemporaryToRealTable($table);
    }
}
