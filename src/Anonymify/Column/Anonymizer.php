<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Anonymify\Column;

use App\Anonymify\Column\Task\AnonymifyTask;
use App\Configuration\Processing;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[WithMonologChannel('anonymize')]
class Anonymizer
{
    /**
     * @param AnonymifyTask[] $tasks
     */
    public function __construct(
        #[AutowireIterator('anonymify_column.task', defaultIndexMethod: 'getName')] private readonly iterable $tasks,
        protected LoggerInterface $logger,
    ) {
    }

    public function anonymize(Processing $config): void
    {
        $this->logger->info('General anonymisation');
        $this->processGeneralTasks($config);
        $this->logger->info('Table anonymisation');
        $this->processTableTasks($config);
    }

    private function processGeneralTasks(Processing $config): void
    {
        foreach ($config->anonymize->general as $definition) {
            $this->logger->info('Column: '.$definition->column);
            foreach ($this->tasks as $name => $task) {
                if ($name === $definition->name) {
                    $task->run($definition, $config->anonymize->tableDefinitions);
                }
            }
        }
    }

    private function processTableTasks(Processing $config): void
    {
        foreach ($config->anonymize->tables as $tableDefinition) {
            foreach ($tableDefinition->columns as $column) {
                $this->logger->info(sprintf('Table %s[%s]', $tableDefinition->name, $column->name));
                foreach ($this->tasks as $name => $task) {
                    if ($name === $column->name) {
                        $task->runForTable($column, $tableDefinition->name);
                    }
                }
            }
        }
    }
}
