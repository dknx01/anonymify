<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Anonymify\Db\Task;

use App\Anonymify\Db\Task\TableStaticTask;
use App\Configuration\Processing;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TableStaticTaskTest extends TestCase
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

    public function testGetPriority(): void
    {
        $this->assertSame(40, TableStaticTask::getPriority());
    }

    public function testRun(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query) => str_starts_with($query, 'UPDATE `foo`')))
            ->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());
        $task = new TableStaticTask(
            $this->entityManager->reveal(),
            $this->logger
        );

        $processingConfig = new \stdClass();
        $processingConfig->anonymize = new \stdClass();
        $processingConfig->anonymize->general = [];
        $processingConfig->anonymize->tables = [];
        $processingConfig->truncate = [];
        $processingConfig->default_masking = new \stdClass();
        $processingConfig->json = [];
        $processingConfig->scripts = [];
        $processingConfig->static_text = [];
        $processingConfig->binary_empty = [];
        $processingConfig->tables = ['foo' => ['bar' => 'Save the clock tower!']];
        $task->run(Processing::fromStdClass($processingConfig));
    }
}
