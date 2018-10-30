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
 * Date: 03/11/18
 * Time: 9.52.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\AbstractJob;
use Bnza\JobRunnerBundle\Job\AbstractTask;
use Bnza\JobRunnerBundle\Job\JobStatus;
use PHPUnit\Framework\MockObject\MockObject;

trait TaskMockTrait
{
    use JobMockTrait;

    /**
     * @var string
     */
    private $dummyTaskMockClass = 'DummyTaskMockClass';

    /**
     * @var string
     */
    private $dummyTaskMockName = 'Dummy task mock name';

    /**
     * @var string
     */
    private $dummyTaskMockNum = 0;

    /**
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param array  $mockedMethods
     * @param bool   $cloneArguments
     *
     * @return MockObject|AbstractTask
     */
    public function getMockForAbstractTask(
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $mockedMethods = [],
        $cloneArguments = false
    ) {
        if (isset($arguments[3])) {
            $arguments[3] = $this->dummyTaskMockNum;
        }

        return $this->getMockForAbstractClass(
            AbstractTask::class,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
    }

    public function generateSuccessfulTaskStub(bool $invokeConstructor = true, string $className = '', string $taskName = '')
    {
        $jobMock = $this->generateSuccessfulJobStub();

        $dataGenerator = function () {
            yield 1;
            yield 2;
        };

        $callable = function ($num) {
            return $num;
        };

        return $this->generateTaskStub($jobMock, $callable, $dataGenerator, $invokeConstructor, $className, $taskName);
    }

    public function generateCallableErrorTaskStub(bool $invokeConstructor = true, string $className = '', string $taskName = '')
    {
        $jobMock = $this->generateSuccessfulJobStub();

        $dataGenerator = function () {
            yield 1;
        };

        $callable = function ($num) {
            return fopen("/non-existent-dir/$num", 'r');
        };

        return $this->generateTaskStub($jobMock, $callable, $dataGenerator, $invokeConstructor, $className, $taskName);
    }

    public function generateCallableCancelledJobTaskStub(bool $invokeConstructor = true, string $className = '', string $taskName = '')
    {
        $jobMock = $this->getMockForAbstractJob([],
            '',
            false,
            false,
            true,
            [
                'getStatus',
                'getObjectManager',
            ]
        );

        $jobMock
            ->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(JobStatus::CANCELLED));

        $jobMock
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->om));

        $dataGenerator = function () {
            yield 1;
            yield 2;
        };

        $callable = function ($num) {
            $job = call_user_func([$this->jobMock, 'cancel']);
        };

        return $this->generateTaskStub($jobMock, $callable, $dataGenerator, $invokeConstructor, $className, $taskName);
    }

    public function generateCallableExceptionTaskStub(bool $invokeConstructor = true, string $className = '', string $taskName = '')
    {
        $jobMock = $this->generateSuccessfulJobStub();

        $dataGenerator = function () {
            yield 1;
        };

        $callable = function ($num) {
            throw new \RuntimeException("Test exception [$num]");
        };

        return $this->generateTaskStub($jobMock, $callable, $dataGenerator, $invokeConstructor, $className, $taskName);
    }

    public function generateTaskStub(
        AbstractJob $jobMock,
        callable $callable,
        callable $dataGenerator,
        bool $invokeConstructor = true,
        string $className = '',
        string $taskName = '')
    {
        // $className = $className ?: $this->dummyTaskMockClass;

        $taskName = $taskName ?: $this->dummyTaskMockName;

        $taskMock = $this->getMockForAbstractTask(
            [
                $jobMock->getObjectManager(),
                $jobMock,
            ],
            '',
            false,
            false,
            true,
            [
                'getName',
                'getData',
                'getCallable',
            ]);

        $taskMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($taskName));

        $taskMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnCallback($dataGenerator));

        $taskMock
            ->expects($this->any())
            ->method('getCallable')
            ->will($this->returnValue($callable));

        if ($invokeConstructor) {
            $this->invokeTaskMockConstructor($taskMock, $jobMock);
        }

        return $taskMock;
    }

    public function invokeTaskMockConstructor(AbstractTask $taskMock, AbstractJob $jobMock)
    {
        $reflectedClass = new \ReflectionClass(AbstractTask::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($taskMock, $this->om, $jobMock, $this->dummyTaskMockNum);
    }
}
