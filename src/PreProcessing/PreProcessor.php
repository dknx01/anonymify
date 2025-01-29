<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\PreProcessing;

use App\Configuration\Processing;
use App\PreProcessing\Task\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class PreProcessor
{
    /**
     * @param Task[] $tasks
     */
    public function __construct(
        #[AutowireIterator('preprocessor.task', defaultPriorityMethod: 'getPriority')] private iterable $tasks,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function preProcess(Processing $preProcessing): void
    {
        $triggers = $this->getTriggers();
        foreach ($triggers as $trigger) {
            $this->entityManager->getConnection()->executeQuery(
                sprintf('DROP TRIGGER `%s`', $trigger)
            );
        }
        foreach ($this->tasks as $task) {
            $task->run($preProcessing);
        }
    }

    /**
     * @return array<array-key, string>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function getTriggers(): array
    {
        $schema = $this->entityManager->getConnection()->getDatabase();
        $sql = <<<SQL
SELECT `TRIGGER_NAME` FROM information_schema.TRIGGERS t
WHERE TRIGGER_SCHEMA= '%s'
SQL;
        $sql = sprintf($sql, $schema);

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchFirstColumn();
    }
}
