<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Obfuscate;

use App\Configuration\Processing;
use App\Obfuscate\Task\ObfuscateTask;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[WithMonologChannel('obfuscate')]
class Obfuscate
{
    /**
     * @param ObfuscateTask[] $tasks
     */
    public function __construct(
        #[AutowireIterator('obfuscate.task', defaultIndexMethod: 'getName')] private iterable $tasks,
        protected LoggerInterface $logger,
    ) {
    }

    public function obfuscate(Processing $config): void
    {
        $this->logger->info('General obfuscation');
        $this->processGeneralTasks($config);
        $this->logger->info('Table obfuscation');
        $this->processTableTasks($config);
    }

    private function processGeneralTasks(Processing $config): void
    {
        foreach ($config->obfuscate->general as $definition) {
            $this->logger->info('Column: '.$definition->column);
            foreach ($this->tasks as $name => $task) {
                if ($name === $definition->name) {
                    $task->run($definition, $config->obfuscate->tableDefinitions);
                }
            }
        }
    }

    private function processTableTasks(Processing $config): void
    {
        foreach ($config->obfuscate->tables as $tableDefinition) {
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
