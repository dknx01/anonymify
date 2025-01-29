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
final class VAT extends AnonymifyAbstract implements AnonymifyTask
{
    private const UST_IDS = [
        'AT' => 'ATU 9999999',
        'BE' => 'BE 9999999999',
        'BG' => 'BG 999999999',
        'CY' => 'CY 99999999X',
        'CZ' => 'CZ 999999999',
        'DE' => 'DE 999999999',
        'DK' => 'DK 99999999',
        'EE' => 'EE 999999999',
        'EL' => 'EL 999999999',
        'ES' => 'ES 99999999X',
        'FI' => 'FI 99999999',
        'FR' => 'FR 1X999999999',
        'HR' => 'HR 99999999999',
        'HU' => 'HU 99999999',
        'IE' => 'IE 9999999QA',
        'IT' => 'IT 99999999999',
        'LT' => 'LT 999999999',
        'LU' => 'LU 99999999',
        'LV' => 'LV 99999999999',
        'MT' => 'MT 99999999',
        'NL' => 'NL 999999999B99',
        'PL' => 'PL 9999999999',
        'PT' => 'PT 999999999',
        'RO' => 'RO 999999999',
        'SE' => 'SE 999999999999',
        'SI' => 'SI 99999999',
        'SK' => 'SK 9999999999',
        'XI' => 'XI 999999999',
        'GB' => 'GB 999999999',
        'SM' => 'SM 99999',
    ];

    public static function getName(): string
    {
        return 'vat';
    }

    /**
     * @throws Exception
     */
    public function run(Definition $definition, TableDefinitions $tableDefinitions): void
    {
        $this->logger->info('VAT generation');
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
        $this->logger->info('VAT generation');
        $this->logger->debug(sprintf('Column %s', $definition->column));

        $this->processTable($table, $definition);
    }

    private function getUstId(string $column): string
    {
        return self::UST_IDS[substr($column, 0, 2)] ?? '';
    }

    /**
     * @throws Exception
     */
    protected function processTable(string $table, Definition $definition): void
    {
        $this->generateTemporaryTable($table);
        foreach ($this->getRows($table) as $row) {
            if (!$this->isColumnBlank($row[$definition->column] ?? null)) {
                $row[$definition->column] = $this->getUstId($row[$definition->column]);
            }
            $this->insertData($row);
            unset($row);
        }
        $this->copyDataFromTemporaryToRealTable($table);
    }
}
