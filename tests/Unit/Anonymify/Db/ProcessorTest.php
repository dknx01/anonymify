<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Anonymify\Db;

use App\Anonymify\Db\Processor;
use App\Anonymify\Db\Task\Task;
use App\Configuration\Processing;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testPreProcess(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->getDatabase()->shouldBeCalled()->willReturn('test');
        $triggersResult = $this->prophesize(Result::class);
        $triggersResult->fetchFirstColumn()->shouldBeCalled()->willReturn(['trigger1']);
        $connection->executeQuery(Argument::that(static fn (string $query) => str_contains($query, 'TRIGGERS')))
            ->shouldBeCalled()->willReturn($triggersResult->reveal());
        $connection->executeQuery(Argument::that(static fn (string $query) => str_contains($query, 'DROP TRIGGER')))
            ->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());

        $entityManager = $this->prophesize(EntityManager::class);
        $entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

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
        $processingConfig->tables = [];

        $task = $this->prophesize(Task::class);
        $task->run(Argument::type(Processing::class))->shouldBeCalled();
        $tasks = [$task->reveal()];
        $processor = new Processor($tasks, $entityManager->reveal());
        $processor->process(Processing::fromStdClass($processingConfig));
    }
}
