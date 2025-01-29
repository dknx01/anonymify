<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Anonymify\Column\Task;

use App\Configuration\Anonymify\Definition;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract class AnonymifyAbstract
{
    private string $tmpName;

    public function __construct(
        #[Autowire('@Faker')] protected Generator $faker,
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * @throws Exception
     */
    public function generateTemporaryTable(string $table): void
    {
        $this->tmpName = 'ANONYMIFY_'.$table;
        $this->entityManager->getConnection()->executeQuery(
            "CREATE TABLE IF NOT EXISTS {$this->tmpName} LIKE {$table}"
        );
    }

    /**
     * @throws Exception
     */
    public function copyDataFromTemporaryToRealTable(string $table): void
    {
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $this->entityManager->getConnection()->executeStatement("DROP TABLE IF EXISTS {$table}");
        $this->entityManager->getConnection()->executeStatement("RENAME TABLE {$this->tmpName} TO {$table}");
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @param array<string, mixed> $row
     *
     * @throws Exception
     */
    public function insertData(array $row): void
    {
        $this->entityManager->getConnection()->insert($this->tmpName, $row);
    }

    /**
     * @return array<array-key, string>
     *
     * @throws Exception
     */
    protected function getTablesWithColumns(string $column): array
    {
        $schema = $this->entityManager->getConnection()->getDatabase();
        $sql = <<<SQL
SELECT TABLE_NAME AS `table`, COLUMN_NAME
FROM information_schema.COLUMNS c WHERE c.table_schema = '%s' AND c. COLUMN_NAME ='%s' AND c.TABLE_NAME IN(SELECT t.TABLE_NAME from information_schema.TABLES t WHERE t.TABLE_SCHEMA = '%s' and TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME NOT LIKE '%s');
SQL;
        $result = $this->entityManager->getConnection()->executeQuery(
            sprintf($sql, $schema, $column, $schema, 'ANONYMIFY_%'),
        );
        $tableColumns = $result->fetchAllAssociative();
        $tableColumns = $this->ensureCaseSensitiveColumnName($tableColumns, $column);

        return array_column($tableColumns, 'table');
    }

    /**
     * @param array<array-key, array<string, string>> $tableColumns
     *
     * @return array<array-key, array<string, string>>
     */
    private function ensureCaseSensitiveColumnName(array $tableColumns, string $column): array
    {
        return array_filter($tableColumns, static fn (array $table) => $table['COLUMN_NAME'] === $column);
    }

    protected function isColumnBlank(mixed $value): bool
    {
        return null === $value || '' === $value;
    }

    /**
     * @return \Traversable<int,array<string,mixed>>
     *
     * @throws Exception
     */
    protected function getRows(string $table): \Traversable
    {
        return $this->entityManager->getConnection()->executeQuery("SELECT * FROM {$table}")->iterateAssociative();
    }

    /**
     * @throws Exception
     */
    abstract protected function processTable(string $table, Definition $definition): void;
}
