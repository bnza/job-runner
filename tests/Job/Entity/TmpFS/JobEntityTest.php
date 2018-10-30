<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 28/10/18
 * Time: 15.16.
 */

namespace Bnza\JobRunnerBundle\Tests\Job\Entity\TmpFS;

use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\TaskEntity;

/**
 * Class JobEntityTest.
 */
class JobEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyConstructor()
    {
        $entity = new JobEntity();
        $this->assertTrue(ctype_xdigit($entity->getId()));
        $this->assertEquals(40, strlen($entity->getId()));

        return $entity;
    }

    public function testConstructor()
    {
        $sha1 = '75228a0dd3e56aa2203d50c5867eac759ef6f322';
        $entity = new JobEntity($sha1);
        $this->assertEquals($sha1, $entity->getId());

        return $entity;
    }

    /**
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /".+" is not a valid sha1 hash/
     */
    public function testInvalidIdConstructor()
    {
        $sha1 = 'Not sha1 hash';
        $entity = new JobEntity($sha1);
    }

    /**
     * @depends testEmptyConstructor
     *
     * @param JobEntity $entity
     */
    public function testSetGetClass(JobEntity $entity)
    {
        $entity->setClass(self::class);
        $this->assertEquals(self::class, $entity->getClass());
    }

    /**
     * @depends testEmptyConstructor
     *
     * @param JobEntity $entity
     */
    public function testSetGetTasksNum(JobEntity $entity)
    {
        $entity->setTasksNum(4);
        $this->assertEquals(4, $entity->getTasksNum());
    }

    /**
     * @depends testEmptyConstructor
     *
     * @param JobEntity $entity
     */
    public function testSetGetError(JobEntity $entity)
    {
        $entity->setError('Bad error');
        $this->assertEquals('Bad error', $entity->getError());
    }

    /**
     * @depends testEmptyConstructor
     *
     * @param JobEntity $entity
     */
    public function testSetGetName(JobEntity $entity)
    {
        $entity->setName('Entity name');
        $this->assertEquals('Entity name', $entity->getName());
    }

    /**
     * @depends testEmptyConstructor
     *
     * @param JobEntity $entity
     */
    public function testAddTask(JobEntity $entity)
    {
        $num = 3;
        for ($i = 0; $i < $num; ++$i) {
            $task = new TaskEntity($entity, $i);
            $entity->addTask($task);
            $this->assertEquals($entity, $task->getJob());
        }

        $this->assertEquals($num, $entity->getTasks()->count());
    }

    public function taskNumProvider()
    {
        return [
            [23],
            [45],
            [47],
        ];
    }

    /**
     * @depends      testEmptyConstructor
     * @dataProvider taskNumProvider
     *
     * @param $num
     * @param JobEntity $entity
     */
    public function testGetTask(int $num, JobEntity $entity)
    {
        $task = new TaskEntity($entity, $num);
        $entity->addTask($task);
        $this->assertEquals($num, $entity->getTask($num)->getNum());
    }

    /**
     * @depends testEmptyConstructor
     * @expectedException              \RuntimeException
     * @expectedExceptionMessageRegExp /No tasks at index \d+/
     *
     * @param JobEntity $entity
     */
    public function testGetTaskNotFoundException(JobEntity $entity)
    {
        $entity->getTask(8);
    }

    /**
     * @expectedException              \LogicException
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
