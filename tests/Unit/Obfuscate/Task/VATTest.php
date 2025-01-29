<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Obfuscate\Task;

use App\Configuration\Obfuscate\Definition;
use App\Configuration\Obfuscate\Table;
use App\Configuration\Obfuscate\TableDefinitions;
use App\Obfuscate\Task\VAT;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class VATTest extends TestCase
{
    use ProphecyTrait;
    use TaskMockingTrait;
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
        $this->assertSame('vat', VAT::getName());
    }

    public function testRun(): void
    {
        // mock finding tables with column
        $resultTables = $this->prophesize(Result::class);
        $resultTables->fetchAllAssociative()->shouldBeCalled()->willReturn([
            'tableToBeExcluded' => ['table' => 'tableToBeExcluded', 'COLUMN_NAME' => 'VAT'],
            'table1' => ['table' => 'table1', 'COLUMN_NAME' => 'vat'],
        ]);
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query): bool => str_contains($query, 'information_schema')))
            ->shouldBeCalledOnce()
            ->willReturn($resultTables->reveal());
        $connection->getDatabase()->shouldBeCalled()->willReturn('MyDB');
        // mock temporary table creation
        $this->mockTemporaryTableCreation($connection);

        // mock data in table
        $dataResult = $this->prophesize(Result::class);
        $dataResult->iterateAssociative()->shouldBeCalledOnce()->willReturn(
            new \ArrayObject([['vat' => 'DE 111111111']])
        );
        $connection->executeQuery('SELECT * FROM table1')
            ->shouldBeCalledOnce()
            ->willReturn($dataResult->reveal());
        $connection->insert('OBFUSCATE_table1', ['vat' => 'DE 999999999'])->shouldBeCalledOnce()->willReturn(1);
        $this->mockCopyData($connection);
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $tableDefinition = new TableDefinitions([]);
        $definition = new Definition('vat', null);
        $this->getTask()->run($definition, $tableDefinition);
    }

    public function testRunWithTableExclusion(): void
    {
        // mock finding tables with column
        $resultTables = $this->prophesize(Result::class);
        $resultTables->fetchAllAssociative()->shouldBeCalled()->willReturn([
            'table1' => ['table' => 'table1', 'COLUMN_NAME' => 'vat'],
            'table2' => ['table' => 'table2', 'COLUMN_NAME' => 'vat'],
        ]);
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query): bool => str_contains($query, 'information_schema')))
            ->shouldBeCalledOnce()
            ->willReturn($resultTables->reveal());
        $connection->getDatabase()->shouldBeCalled()->willReturn('MyDB');
        // mock temporary table creation
        $this->mockTemporaryTableCreation($connection);

        // mock data in table
        $dataResult = $this->prophesize(Result::class);
        $dataResult->iterateAssociative()->shouldBeCalledOnce()->willReturn(
            new \ArrayObject([['vat' => 'DE 111111111']])
        );
        $connection->executeQuery('SELECT * FROM table1')
            ->shouldBeCalledOnce()
            ->willReturn($dataResult->reveal());

        $connection->insert('OBFUSCATE_table1', ['vat' => 'DE 999999999'])->shouldBeCalledOnce()->willReturn(1);
        $this->mockCopyData($connection);
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $tableDefinition = new TableDefinitions(['table2' => new Table('table2', ['vat' => null])]);
        $definition = new Definition('vat', null);
        $this->getTask()->run($definition, $tableDefinition);
    }

    public function testRunForTable(): void
    {
        // mock finding tables with column
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query): bool => str_contains($query, 'information_schema')))
            ->shouldNotBeCalled();
        $connection->getDatabase()->shouldNotBeCalled();
        // mock temporary table creation
        $this->mockTemporaryTableCreation($connection);

        // mock data in table
        $dataResult = $this->prophesize(Result::class);
        $dataResult->iterateAssociative()->shouldBeCalledOnce()->willReturn(
            new \ArrayObject([['vat' => 'DE 111111111']])
        );
        $connection->executeQuery('SELECT * FROM table1')
            ->shouldBeCalledOnce()
            ->willReturn($dataResult->reveal());
        $connection->insert('OBFUSCATE_table1', ['vat' => 'DE 999999999'])->shouldBeCalledOnce()->willReturn(1);
        $this->mockCopyData($connection);
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $tableDefinition = new TableDefinitions([]);
        $definition = new Definition('vat', null);
        $this->getTask()->runForTable($definition, 'table1');
    }

    private function getTask(): VAT
    {
        return new VAT(
            Factory::create('de_DE'),
            $this->entityManager->reveal(),
            $this->logger
        );
    }
}
