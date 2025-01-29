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

#[WithMonologChannel('anonymize')]
final class DefaultTask extends AnonymifyAbstract implements AnonymifyTask
{
    public static function getName(): string
    {
        return 'default';
    }

    /**
     * @throws Exception
     */
    public function run(Definition $definition, TableDefinitions $tableDefinitions): void
    {
        $this->logger->info('Default generation');
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
        $this->logger->info('Default generation');
        $this->logger->debug(sprintf('Column %s', $definition->column));

        $this->logger->debug(sprintf('Table %s', $table));
        $this->processTable($table, $definition);
    }

    private function masking(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        $middle = substr($value, 1, -1);
        $middle = str_repeat('*', mb_strlen($middle, 'UTF-8'));

        return $value[0].$middle.substr($value, -1);
    }

    /**
     * @throws Exception
     */
    protected function processTable(string $table, Definition $definition): void
    {
        $this->generateTemporaryTable($table);
        foreach ($this->getRows($table) as $row) {
            if (!$this->isColumnBlank($row[$definition->column] ?? null)) {
                $row[$definition->column] = $this->masking($row[$definition->column]);
            }
            $this->insertData($row);
            unset($row);
        }
        $this->copyDataFromTemporaryToRealTable($table);
    }
}
