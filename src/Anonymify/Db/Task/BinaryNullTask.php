<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Anonymify\Db\Task;

use App\Configuration\Processing;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('anonymize')]
readonly class BinaryNullTask implements Task
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function run(Processing $config): void
    {
        $this->logger->info('Binary column NULL');
        foreach ($config->binaryEmpty as $table => $columns) {
            foreach ($columns as $column) {
                $this->logger->debug(sprintf('Table: %s (%s)', $table, $column));
                $sql = "UPDATE `{$table}` SET `{$column}` = NULL";
                $this->entityManager->getConnection()->executeQuery($sql);
            }
        }
    }

    public static function getPriority(): int
    {
        return 70;
    }
}
