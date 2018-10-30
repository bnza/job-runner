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
 * Time: 9.08.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\AbstractJob;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Bnza\JobRunnerBundle\Job\Dummy\DummyTask;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use PHPUnit\Framework\MockObject\MockObject;

trait JobMockTrait
{

    /**
     * @var string
     */
    private $dummyJobMockName = 'Dummy job mock name';

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var ParameterBag
     */
    private $pb;

    /**
     * @param array  $arguments
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param array  $mockedMethods
     * @param bool   $cloneArguments
     *
     * @return MockObject|AbstractJob
     */
    public function getMockForAbstractJob(
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $mockedMethods = [],
        $cloneArguments = false
    ) {
        // Set up default arguments
        $args = [
            [EventDispatcher::class, 'dispatcher'],
            [JobTmpFSObjectManager::class, 'om'],
            [ParameterBag::class, 'pb'],
        ];

        // $mockClassName = $mockClassName ?: $this->dummyJobMockClass;

        for ($i = 0; $i < count($args); ++$i) {
            if (!array_key_exists($i, $arguments)) {
                $arguments[$i] = new $args[$i][0]();
            } else {
                $arguments[$i] ?: new $args[$i][0]();
            }
            $this->{$args[$i][1]} = $arguments[$i];
        }

        return $this->getMockForAbstractClass(
            AbstractJob::class,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
    }

    /**
     * @param bool   $invokeConstructor
     * @param string $className
     * @param string $jobName
     *
     * @return AbstractJob|MockObject
     */
    public function generateSuccessfulJobStub(bool $invokeConstructor = true, string $className = '', string $jobName = '')
    {
        $jobName = $jobName ?: $this->dummyJobMockName;

        $generator = function () {
            yield [DummyTask::class];
            yield [DummyTask::class];
        };

        return $this->generateJobStub(
            $generator,
            $invokeConstructor,
            $className,
            $jobName
        );
    }

    /**
     * @param bool   $invokeConstructor
     * @param string $className
     * @param string $jobName
     *
     * @return AbstractJob|MockObject
     */
    public function generateWrongTaskClassJobStub(bool $invokeConstructor = true, string $className = '', string $jobName = '')
    {
        $jobName = $jobName ?: $this->dummyJobMockName;

        $generator = function () {
            yield [\Exception::class];
        };

        return $this->generateJobStub(
            $generator,
            $invokeConstructor,
            $className,
            $jobName
        );
    }

    /**
     * @param callable $getTasksCallable
     * @param bool $invokeConstructor
     * @param string $className
     * @param string $jobName
     *
     * @return AbstractJob|MockObject
     */
    public function generateJobStub(
        callable $getTasksCallable,
        bool $invokeConstructor = true,
        string $className = '',
        string $jobName = ''
    )
    {
        $jobName = $jobName ?: $this->dummyJobMockName;

        $jobMock = $this->getMockForAbstractJob(
            [],
            $className,
            false,
            false,
            true
        );

        $jobMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($jobName));

        $jobMock
            ->expects($this->any())
            ->method('getTasks')
            ->will($this->returnCallback($getTasksCallable));

        if ($invokeConstructor) {
            $this->invokeJobMockConstructor($jobMock);
        }

        return $jobMock;
    }

    public function invokeJobMockConstructor(AbstractJob $jobMock)
    {
        $reflectedClass = new \ReflectionClass(AbstractJob::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($jobMock, $this->dispatcher, $this->om, $this->pb);
    }
}
