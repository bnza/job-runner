<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 28/10/18
 * Time: 15.16
 */

namespace Bnza\JobRunnerBundle\Tests\Job\Entity\TmpFS;

use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\TaskEntity;

/**
 * Class JobEntityTest
 * @package Bnza\JobRunnerBundle\Tests\Job\Entity\TmpFS
 */
class JobEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyConstructor()
    {
        $entity = new JobEntity();
        $this->assertTrue(ctype_xdigit($entity->getId()));
        $this->assertEquals(40, strlen($entity->getId()));
    }

    public function testConstructor()
    {
        $sha1 = "75228a0dd3e56aa2203d50c5867eac759ef6f322";
        $entity = new JobEntity($sha1);
        $this->assertEquals($sha1, $entity->getId());
    }

    /**
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /".+" is not a valid sha1 hash/
     *
     */
    public function testInvalidIdConstructor()
    {
        $sha1 = "Not sha1 hash";
        $entity = new JobEntity($sha1);
    }

    public function testSetGetClass()
    {
        $entity = new JobEntity();
        $entity->setClass(self::class);
        $this->assertEquals(self::class, $entity->getClass());
    }

    public function testAddTask()
    {
        $entity = new JobEntity();

        foreach ([34, 56, 76] as $num) {
            $task = new TaskEntity($entity, $num);
            $entity->addTask($task);
            $this->assertEquals($entity, $task->getJob());
        }
        $this->assertEquals(3, $entity->getTasks()->count());
    }

    public function testGetTask()
    {
        $entity = new JobEntity();
        foreach ([34, 56, 76] as $num) {
            $task = new TaskEntity($entity, $num);
            $entity->addTask($task);
            $this->assertEquals($num, $entity->getTask($num)->getNum());
        }
    }

    /**
     * @expectedException              \LogicException
     *
     */
    public function testDuplicateTaskNum()
    {
        $entity = new JobEntity();
        $task1 = new TaskEntity($entity, 0);
        $task2 = new TaskEntity($entity, 0);
        $entity->addTask($task1);
        $entity->addTask($task2);
    }
}