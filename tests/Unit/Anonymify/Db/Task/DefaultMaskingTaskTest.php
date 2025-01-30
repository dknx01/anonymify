<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Anonymify\Db\Task;

use App\Anonymify\Db\Task\DefaultMaskingTask;
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

class DefaultMaskingTaskTest extends TestCase
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
        $this->assertSame(90, DefaultMaskingTask::getPriority());
    }

    public function testRun(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query) => str_starts_with($query, 'UPDATE')))
            ->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());

        $resultTables = $this->prophesize(Result::class);
        $resultTables->fetchFirstColumn()->shouldBeCalled()->willReturn([
            'table1', 'table2',
        ]);
        $connection->executeQuery(Argument::that(static fn ($query): bool => str_contains($query, 'information_schema')))
            ->shouldBeCalledOnce()
            ->willReturn($resultTables->reveal());
        $connection->getDatabase()->shouldBeCalled()->willReturn('MyDB');

        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $processingConfig = new \stdClass();
        $processingConfig->anonymize = new \stdClass();
        $processingConfig->anonymize->general = [];
        $processingConfig->anonymize->tables = [];
        $processingConfig->truncate = [];
        $processingConfig->default_masking = new \stdClass();
        $processingConfig->default_masking->fooColumn = [
            'table2',
        ];
        $processingConfig->json = [];
        $processingConfig->scripts = [];
        $processingConfig->static_text = [];
        $processingConfig->binary_empty = [];
        $processingConfig->tables = [];

        (new DefaultMaskingTask($this->entityManager->reveal(), $this->logger))
            ->run(Processing::fromStdClass($processingConfig));
    }

    public function testRunWithoutColumn(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query) => str_starts_with($query, 'UPDATE')))
            ->shouldNotBeCalled();

        $resultTables = $this->prophesize(Result::class);
        $resultTables->fetchFirstColumn()->shouldBeCalled()->willReturn([]);
        $connection->executeQuery(Argument::that(static fn ($query): bool => str_contains($query, 'information_schema')))
            ->shouldBeCalledOnce()
            ->willReturn($resultTables->reveal());
        $connection->getDatabase()->shouldBeCalled()->willReturn('MyDB');

        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $processingConfig = new \stdClass();
        $processingConfig->anonymize = new \stdClass();
        $processingConfig->anonymize->general = [];
        $processingConfig->anonymize->tables = [];
        $processingConfig->truncate = [];
        $processingConfig->default_masking = new \stdClass();
        $processingConfig->default_masking->fooColumn = null;
        $processingConfig->json = [];
        $processingConfig->scripts = [];
        $processingConfig->static_text = [];
        $processingConfig->binary_empty = [];
        $processingConfig->tables = [];

        (new DefaultMaskingTask($this->entityManager->reveal(), $this->logger))
            ->run(Processing::fromStdClass($processingConfig));
    }
}
