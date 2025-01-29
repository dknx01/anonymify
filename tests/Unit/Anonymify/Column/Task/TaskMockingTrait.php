<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Anonymify\Column\Task;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

trait TaskMockingTrait
{
    /**
     * @param Connection|ObjectProphecy<Connection> $connection
     *
     * @throws Exception
     */
    protected function mockTemporaryTableCreation(Connection|ObjectProphecy $connection): void
    {
        $connection->executeQuery(Argument::that(
            static fn (string $query): bool => str_starts_with($query, 'CREATE TABLE')
        ))->shouldBeCalledOnce()->willReturn($this->prophesize(Result::class)->reveal());
    }

    /**
     * @param Connection|ObjectProphecy<Connection> $connection
     *
     * @throws Exception
     */
    protected function mockCopyData(Connection|ObjectProphecy $connection): void
    {
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0')->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());
        $connection->executeStatement(Argument::that(static fn (string $query): bool => str_starts_with($query, 'DROP TABLE IF EXISTS')))->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());
        $connection->executeStatement(Argument::that(static fn (string $query): bool => str_starts_with($query, 'RENAME TABLE')))->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1')->shouldBeCalled()->willReturn($this->prophesize(Result::class)->reveal());
    }
}
