<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Anonymify\Db\Task;

use App\Anonymify\Db\Task\TruncateTask;
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

class TruncateTaskTest extends TestCase
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
        $this->assertSame(100, TruncateTask::getPriority());
    }

    public function testRun(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query) => str_starts_with($query, 'TRUNCATE TABLE')))
            ->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());
        $truncateTask = new TruncateTask(
            $this->entityManager->reveal(),
            $this->logger
        );

        $processingConfig = new \stdClass();
        $processingConfig->anonymize = new \stdClass();
        $processingConfig->anonymize->general = [];
        $processingConfig->anonymize->tables = [];
        $processingConfig->truncate = ['foo' => null];
        $processingConfig->default_masking = new \stdClass();
        $processingConfig->json = [];
        $processingConfig->scripts = [];
        $processingConfig->static_text = [];
        $processingConfig->binary_empty = [];
        $processingConfig->tables = [];
        $truncateTask->run(Processing::fromStdClass($processingConfig));
    }
}
