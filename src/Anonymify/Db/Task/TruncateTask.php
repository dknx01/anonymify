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
readonly class TruncateTask implements Task
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function run(Processing $config): void
    {
        $this->logger->info('Truncating tables');
        foreach ($config->truncate as $table) {
            $this->logger->debug('Table: '.$table);
            $this->entityManager->getConnection()->executeQuery(sprintf('TRUNCATE TABLE `%s`;', $table));
        }
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
