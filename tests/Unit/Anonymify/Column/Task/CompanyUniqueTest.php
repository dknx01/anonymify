<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Anonymify\Column\Task;

use App\Anonymify\Column\Task\CompanyUnique;
use App\Configuration\Anonymify\Definition;
use App\Configuration\Anonymify\Table;
use App\Configuration\Anonymify\TableDefinitions;
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

class CompanyUniqueTest extends TestCase
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
        $this->assertSame('company_unique', CompanyUnique::getName());
    }

    public function testRun(): void
    {
        // mock finding tables with column
        $resultTables = $this->prophesize(Result::class);
        $resultTables->fetchAllAssociative()->shouldBeCalled()->willReturn([
            'table1' => ['table' => 'table1', 'COLUMN_NAME' => 'company'],
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
            new \ArrayObject([['company' => 'Blick and Sons']])
        );
        $connection->executeQuery('SELECT * FROM table1')
            ->shouldBeCalledOnce()
            ->willReturn($dataResult->reveal());
        $connection->insert('ANONYMIFY_table1', Argument::that(static fn ($data) => array_key_exists('company', $data)))
            ->shouldBeCalledOnce()->willReturn(1);
        $this->mockCopyData($connection);
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $tableDefinition = new TableDefinitions([]);
        $definition = new Definition('company', null);
        $this->getTask()->run($definition, $tableDefinition);
    }

    public function testRunWithTableExclusion(): void
    {
        // mock finding tables with column
        $resultTables = $this->prophesize(Result::class);
        $resultTables->fetchAllAssociative()->shouldBeCalled()->willReturn([
            'table1' => ['table' => 'table1', 'COLUMN_NAME' => 'company'],
            'table2' => ['table' => 'table2', 'COLUMN_NAME' => 'company'],
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
            new \ArrayObject([['company' => 'Blick and Sons']])
        );
        $connection->executeQuery('SELECT * FROM table1')
            ->shouldBeCalledOnce()
            ->willReturn($dataResult->reveal());

        $connection->insert('ANONYMIFY_table1', Argument::that(static fn ($data) => array_key_exists('company', $data)))
            ->shouldBeCalledOnce()->willReturn(1);
        $this->mockCopyData($connection);
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $tableDefinition = new TableDefinitions(['table2' => new Table('table2', ['company' => null])]);
        $definition = new Definition('company', null);
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
            new \ArrayObject([['company' => 'Blick and Sons']])
        );
        $connection->executeQuery('SELECT * FROM table1')
            ->shouldBeCalledOnce()
            ->willReturn($dataResult->reveal());
        $connection->insert('ANONYMIFY_table1', Argument::that(static fn ($data) => array_key_exists('company', $data)))
            ->shouldBeCalledOnce()->willReturn(1);
        $this->mockCopyData($connection);
        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $definition = new Definition('company', null);
        $this->getTask()->runForTable($definition, 'table1');
    }

    private function getTask(): CompanyUnique
    {
        return new CompanyUnique(
            Factory::create('de_DE'),
            $this->entityManager->reveal(),
            $this->logger
        );
    }
}
