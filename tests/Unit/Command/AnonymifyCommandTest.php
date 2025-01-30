<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Command;

use App\Anonymify\Column\Anonymizer;
use App\Anonymify\Db\Processor;
use App\Command\AnonymifyCommand;
use App\Configuration\Processing;
use App\Configuration\Reader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AnonymifyCommandTest extends TestCase
{
    use ProphecyTrait;

    public function testCommand(): void
    {
        $reader = new Reader(
            __DIR__.'/../../../data/anonymify.schema.json',
            new Validator()
        );

        $entityManager = $this->prophesize(EntityManager::class);
        $connection = $this->prophesize(Connection::class);
        $connection->getDatabase()->shouldBeCalled()->willReturn('test');

        $triggersResult = $this->prophesize(Result::class);
        $triggersResult->fetchFirstColumn()->shouldBeCalled()->willReturn([]);
        $connection->executeQuery(Argument::that(static fn (string $query) => str_contains($query, 'TRIGGERS')))
            ->shouldBeCalled()->willReturn($triggersResult->reveal());

        $entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $processor = new Processor([], $entityManager->reveal());

        $anonymizer = $this->prophesize(Anonymizer::class);
        $anonymizer->anonymize(Argument::type(Processing::class))->shouldBeCalled();

        $command = new AnonymifyCommand(
            $reader,
            $processor,
            $anonymizer->reveal(),
        );
        $input = new ArrayInput(
            ['--config' => __DIR__.'/../../../data/anonymify.config.json']
        );
        $this->assertSame(Command::SUCCESS, $command->run($input, new NullOutput()));
    }

    public function testCommandWithError(): void
    {
        $reader = new Reader(
            __DIR__.'/../../../data/anonymify.schema.json',
            new Validator()
        );

        $entityManager = $this->prophesize(EntityManager::class);

        $processor = new Processor([], $entityManager->reveal());

        $anonymizer = $this->prophesize(Anonymizer::class);

        $command = new AnonymifyCommand(
            $reader,
            $processor,
            $anonymizer->reveal(),
        );
        $input = new ArrayInput(
            ['--config' => __DIR__.'/../Configuration/data/anonymify_error.config.json']
        );

        $this->assertSame(Command::FAILURE, $command->run($input, new NullOutput()));
    }
}
