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
readonly class TableStaticTask implements Task
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function run(Processing $config): void
    {
        $this->logger->info('Table static text');
        foreach ($config->tables as $table => $definition) {
            foreach ($definition as $column => $text) {
                $sql = "UPDATE `{$table}` SET `{$column}` = '{$text}'";
                $this->logger->debug(sprintf('Table: %s (%s)', $table, $column));
                $this->entityManager->getConnection()->executeQuery($sql);
            }
        }
    }

    public static function getPriority(): int
    {
        return 40;
    }
}
