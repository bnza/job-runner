<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\AbstractTask;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\JobConnector;
use Bnza\JobRunnerBundle\Job\TaskConnector;

class TaskConnectorTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSFixturesTrait;

    protected function getJobConnectorMock(JobEntity $entity): JobConnector
    {
        $jobConnector = $this->createMock(JobConnector::class);

        $jobConnector
            ->method('getId')
            ->willReturn($entity->getId());

        return $jobConnector;
    }

    protected function getTaskConnector(string $prop = '', $value = 0): TaskConnector
    {
        $taskNum = 0;
        $jobEntity = $this->getPersistedJobEntity();
        $taskEntity = $this->getPersistedTaskEntity($jobEntity, $taskNum, $prop, $value);
        $jobConnector = $this->getJobConnectorMock($jobEntity);

        return new TaskConnector($this->om, $jobConnector, $taskNum);
    }

    public function testConstructor()
    {
        $connector = $this->getTaskConnector();
        $this->assertInstanceOf(TaskConnector::class, $connector);

        return [$connector, $this->taskEntity, $this->om];
    }

    /**
     * @depends testConstructor
     *
     * @param array $params
     */
    public function testGetJob(array $params)
    {
        list($task, $entity, $om) = $params;
        $this->assertInstanceOf(JobConnector::class, $task->getJob());
    }

    /**
     * @depends testConstructor
     *
     * @param array $params
     */
    public function testGetClass(array $params)
    {
        list($task, $entity, $om) = $params;
        $this->assertEquals(AbstractTask::class, $task->getClass());
    }

    public function connectorPropertiesProvider()
    {
        return [
            ['Name', 'Test name'],
            ['StepsNum', 2],
            ['CurrentStepNum', 45],
        ];
    }

    /**
     * @dataProvider connectorPropertiesProvider
     *
     * @param $prop
     * @param $value
     */
    public function testGetProperties($prop, $value)
    {
        $connector = $this->getTaskConnector($prop, $value);

        $method = 'get'.$prop;

        $this->assertEquals($value, $connector->$method());
    }

    /**
     * @depends testConstructor
     *
     * @param array $params
     *
     * @return array
     */
    public function testIsErrorIsFalse(array $params)
    {
        list($task, $entity, $om) = $params;
        $this->assertFalse($task->isError());

        return $params;
    }

    /**
     * @depends testConstructor
     *
     * @param array $params
     */
    public function testIsRunningIsFalse(array $params)
    {
        list($task, $entity, $om) = $params;
        $this->assertFalse($task->isRunning());
    }

    /**
     * @depends testIsErrorIsFalse
     *
     * @param array $params
     *
     * @return array
     */
    public function testIsRunningIsTrue(array $params)
    {
        list($task, $entity, $om) = $params;
        $entity
            ->setStepsNum(3)
            ->setCurrentStepNum(2)
        ;
        $om->persist($entity);
        $this->assertTrue($task->isRunning());

        return $params;
    }

    /**
     * @depends testIsRunningIsTrue
     *
     * @param array $params
     *
     * @return array
     */
    public function testIsRunningIsFalseWhenStepsEnded(array $params)
    {
        list($task, $entity, $om) = $params;
        $entity
            ->setStepsNum(3)
            ->setCurrentStepNum(3)
        ;
        $om->persist($entity);
        //$connector = $this->getTaskConnector('Error', 'Bad error');
        $this->assertFalse($task->isRunning());

        return $params;
    }

    /**
     * @depends testIsRunningIsTrue
     *
     * @param array $params
     *
     * @return array
     */
    public function testIsRunningIsFalseWhenError(array $params)
    {
        list($task, $entity, $om) = $params;
        $entity
            ->setError('Bad error')
        ;
        $om->persist($entity);
        //$connector = $this->getTaskConnector('Error', 'Bad error');
        $this->assertFalse($task->isRunning());

        return $params;
    }

    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}
