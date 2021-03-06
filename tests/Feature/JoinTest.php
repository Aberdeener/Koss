<?php

use Aberdeener\Koss\Koss;
use PHPUnit\Framework\TestCase;
use Aberdeener\Koss\Queries\Joins\Join;
use Aberdeener\Koss\Queries\Joins\InnerJoin;
use Aberdeener\Koss\Exceptions\JoinException;
use Aberdeener\Koss\Queries\Joins\LeftOuterJoin;
use Aberdeener\Koss\Queries\Joins\RightOuterJoin;

/**
 * @uses Aberdeener\Koss\Koss
 * @uses Aberdeener\Koss\Util\Util
 *
 * @covers Aberdeener\Koss\Queries\Joins\Join
 * @covers Aberdeener\Koss\Exceptions\JoinException
 * @covers Aberdeener\Koss\Queries\Joins\InnerJoin
 * @covers Aberdeener\Koss\Queries\Joins\LeftOuterJoin
 * @covers Aberdeener\Koss\Queries\Joins\RightOuterJoin
 * @covers Aberdeener\Koss\Queries\SelectQuery
 */
class JoinTest extends TestCase
{
    private Koss $koss;

    public function setUp(): void
    {
        $this->koss = new Koss('localhost', 3306, 'koss', 'root', '');
    }

    public function testCannotMakeJoinSubclassWithInvalidKeyword()
    {
        $this->expectException(JoinException::class);

        new Join('NULL', $this->koss->getAll('users'));
    }

    public function testInnerJoin()
    {
        $this->assertEquals(
            'SELECT * FROM `users` INNER JOIN `users_groups` ON `users_groups`.`users_id` = `users`.`users_id`',
            $this->koss->getAll('users')->innerJoin(function (InnerJoin $join) {
                $join->table('users_groups')->on('users_id');
            })->build()
        );
    }

    public function testInnerJoinCannotUseOnBeforeTableSet()
    {
        $this->expectException(JoinException::class);

        $this->koss->getAll('users')->innerJoin(function (InnerJoin $join) {
            $join->on('user_id');
        });
    }

    public function testInnerJoinUsingThrough()
    {
        $this->assertEquals(
            'SELECT * FROM `users` INNER JOIN `users_groups` ON `users_groups`.`user_id` = `users`.`id` INNER JOIN `groups` ON `groups`.`id` = `users_groups`.`group_id`',
            $this->koss->getAll('users')->innerJoin(function (InnerJoin $join) {
                $join->table('users_groups')->on('user_id', 'id');
                $join->table('groups')->through('users_groups')->on('id', 'group_id');
            })->build()
        );
    }

    public function testLeftOuterJoin()
    {
        $this->assertEquals(
            'SELECT * FROM `users` LEFT OUTER JOIN `users_groups` ON `users_groups`.`users_id` = `users`.`users_id`',
            $this->koss->getAll('users')->leftOuterJoin(function (LeftOuterJoin $join) {
                $join->table('users_groups')->on('users_id');
            })->build()
        );
    }

    public function testRightOuterJoin()
    {
        $this->assertEquals(
            'SELECT * FROM `users` RIGHT OUTER JOIN `users_groups` ON `users_groups`.`users_id` = `users`.`users_id`',
            $this->koss->getAll('users')->rightOuterJoin(function (RightOuterJoin $join) {
                $join->table('users_groups')->on('users_id');
            })->build()
        );
    }
}
