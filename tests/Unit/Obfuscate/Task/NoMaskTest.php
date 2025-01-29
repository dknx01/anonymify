<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Obfuscate\Task;

use App\Configuration\Obfuscate\Definition;
use App\Configuration\Obfuscate\Table;
use App\Configuration\Obfuscate\TableDefinitions;
use App\Obfuscate\Task\NoMask;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class NoMaskTest extends TestCase
{
    use ProphecyTrait;
    /** @var ObjectProphecy<EntityManager> */
    private ObjectProphecy $entityManager;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManager::class);
        $this->logger = new NullLogger();
    }

    public function testGetName(): void
    {
        $this->assertSame('nomask', NoMask::getName());
    }

    public function testRunForTable(): void
    {
        $this->entityManager->getConnection()->shouldNotBeCalled();

        $task = $this->getTask();
        $definition = new Definition('fooColumn', null);
        $task->runForTable($definition, 'barTable');
    }

    public function testRun(): void
    {
        $this->entityManager->getConnection()->shouldNotBeCalled();

        $task = $this->getTask();
        $definition = new Definition('fooColumn', null);
        $tableDefinition = new TableDefinitions([new Table('barTable', [])]);
        $task->run($definition, $tableDefinition);
    }

    private function getTask(): NoMask
    {
        return new NoMask(
            Factory::create('de_DE'),
            $this->entityManager->reveal(),
            $this->logger
        );
    }
}
