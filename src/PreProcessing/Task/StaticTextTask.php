<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\PreProcessing\Task;

use App\Configuration\Processing;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('obfuscate')]
readonly class StaticTextTask implements Task
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function run(Processing $config): void
    {
        $this->logger->info('Static Text');
        $definitions = $this->getTablesWithColumns($config->staticText);
        foreach ($definitions as $column => $definition) {
            if (0 === count($definition)) {
                continue;
            }
            foreach ($definition as $table) {
                $sql = "UPDATE `{$table}` SET `{$column}` = '{$config->staticText[$column]}'";
                $this->logger->debug(sprintf('Table: %s (%s)', $table, $column));
                $this->entityManager->getConnection()->executeQuery($sql);
            }
        }
    }

    public static function getPriority(): int
    {
        return 60;
    }

    /**
     * @param array<string, string> $columns
     *
     * @return array<string, array<array-key, string>>
     *
     * @throws Exception
     */
    private function getTablesWithColumns(array $columns): array
    {
        $definitions = [];
        $schema = $this->entityManager->getConnection()->getDatabase();
        $sql = <<<SQL
Select TABLE_NAME as `table` from information_schema.COLUMNS c where c.table_schema = '%s' and c. COLUMN_NAME ='%s' and c.TABLE_NAME IN(SELECT t.TABLE_NAME from information_schema.TABLES t WHERE t.TABLE_SCHEMA = '%s' and TABLE_TYPE = 'BASE TABLE');
SQL;
        foreach (array_keys($columns) as $column) {
            $result = $this->entityManager->getConnection()->executeQuery(
                sprintf($sql, $schema, $column, $schema),
            );
            $definitions[$column] = $result->fetchFirstColumn();
        }

        return $definitions;
    }
}
