<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Anonymify\Column;

use App\Anonymify\Column\Anonymizer;
use App\Anonymify\Column\Task\DefaultTask;
use App\Configuration\Processing;
use App\Tests\Unit\Anonymify\Column\Task\TaskMockingTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\NullLogger;

class AnonymizerTest extends TestCase
{
    use ProphecyTrait;
    use TaskMockingTrait;
    /** @var ObjectProphecy<EntityManager> */
    private ObjectProphecy $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManager::class);
    }

    /** @todo test Tables in anonymize */
    public function testObfuscate(): void
    {
        $resultTables = $this->prophesize(Result::class);
        $resultTables->fetchAllAssociative()->shouldBeCalled()->willReturn([
            'table1' => ['table' => 'table1', 'COLUMN_NAME' => 'name'],
        ]);
        $connection = $this->prophesize(Connection::class);
        $connection->executeQuery(Argument::that(static fn ($query): bool => str_contains($query, 'information_schema')))
            ->shouldBeCalledOnce()
            ->willReturn($resultTables->reveal());

        $dataResult = $this->prophesize(Result::class);
        $dataResult->iterateAssociative()->shouldBeCalledOnce()->willReturn(
            new \ArrayObject([['name' => 'Barbados Slim']])
        );
        $connection->executeQuery('SELECT * FROM table1')
            ->shouldBeCalledOnce()
            ->willReturn($dataResult->reveal());

        $connection->getDatabase()->shouldBeCalled()->willReturn('MyDB');
        // mock temporary table creation
        $this->mockTemporaryTableCreation($connection);
        $this->mockCopyData($connection);

        $this->entityManager->getConnection()->shouldBeCalled()->willReturn($connection->reveal());

        $connection->insert('ANONYMIFY_table1', ['name' => 'B***********m'])
            ->shouldBeCalledOnce()->willReturn(1);
        $logger = new NullLogger();

        $tasks = [
            DefaultTask::getName() => new DefaultTask(Factory::create('de_DE'), $this->entityManager->reveal(), $logger),
        ];

        $json = new \stdClass();
        $json->anonymize = new \stdClass();
        $json->anonymize->general = [
            'name' => null,
        ];
        $json->truncate = [];
        $json->default_masking = new \stdClass();

        $config = Processing::fromStdClass($json);

        $anonymizer = new Anonymizer($tasks, $logger);
        $anonymizer->obfuscate($config);
    }
}
