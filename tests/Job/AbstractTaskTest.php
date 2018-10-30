<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Exception\JobRunnerException;
use Bnza\JobRunnerBundle\Job\AbstractJob;
use Bnza\JobRunnerBundle\Job\AbstractTask;

class AbstractTaskTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSFixturesTrait;
    use TaskMockTrait;

    public function testSuccessfulConstructor()
    {
        $taskMock = $this->generateSuccessfulTaskStub();
        $this->assertInstanceOf(AbstractTask::class, $taskMock);

        return $taskMock;
    }

    /**
     * @depends testSuccessfulConstructor
     *
     * @param AbstractTask $task
     */
    public function testGetJob(AbstractTask $task)
    {
        $this->assertInstanceOf(AbstractJob::class, $task->getJob());
    }

    /**
     * @depends testSuccessfulConstructor
     *
     * @param AbstractTask $task
     */
    public function testGetName(AbstractTask $task)
    {
        $this->assertEquals($this->dummyTaskMockName, $task->getName());
    }

    /**
     * @depends testSuccessfulConstructor
     *
     * @param AbstractTask $task
     *
     * @return AbstractTask
     */
    public function testGetCurrentStepBeforeRunning(AbstractTask $task)
    {
        $this->assertEquals(0, $task->getCurrentStepNum());

        return $task;
    }

    /**
     * @depends testGetCurrentStepBeforeRunning
     *
     * @param AbstractTask $task
     *
     * @return AbstractTask
     */
    public function testAfterExecuteJobIsNotRunning(AbstractTask $task)
    {
        $task->execute();
        $this->assertFalse($task->isRunning());

        return $task;
    }

    /**
     * @depends testAfterExecuteJobIsNotRunning
     *
     * @param AbstractTask $task
     *
     * @return AbstractTask
     */
    public function testGetCurrentStepAfterRunning(AbstractTask $task)
    {
        $this->assertEquals(2, $task->getCurrentStepNum());

        return $task;
    }

    /**
     * @expectedException \Bnza\JobRunnerBundle\Exception\JobRunnerException
     */
    public function testExecuteCallbackErrorThrowsJobRunnerException()
    {
        $task = $this->generateCallableErrorTaskStub();
        $task->execute();
    }

    /**
     * @expectedException \Bnza\JobRunnerBundle\Exception\JobRunnerException
     * @expectedExceptionMessageRegExp  /Test exception \[\d+\]/
     */
    public function testExecuteCallbackExceptionThrowsJobRunnerException()
    {
        $task = $this->generateCallableExceptionTaskStub();
        $task->execute();
    }

    /**
     * @expectedException \Bnza\JobRunnerBundle\Exception\JobRunnerJobCancelledException
     */
    public function testExecuteCallbackCancelThrowsJobRunnerJobCancelledException()
    {
        $task = $this->generateCallableCancelledJobTaskStub();
        $task->execute();
    }

    public function testExecuteCallbackErrorSetTaskError()
    {
        $task = $this->generateCallableErrorTaskStub();
        try {
            $task->execute();
        } catch (JobRunnerException $e) {
            $this->assertTrue($task->isError());
        }
    }

    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}
