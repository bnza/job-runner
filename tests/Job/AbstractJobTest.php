<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\AbstractJob;
use Bnza\JobRunnerBundle\Job\JobStatus;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

class AbstractJobTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSFixturesTrait;
    use JobMockTrait;

    /**
     * @var \Generator
     */
    private $tasks;

    public function testMockConstructor()
    {
        $jobMock = $this->getMockForAbstractJob(
            [],
            '',
            true,
            false,
            true
        );

        $this->assertInstanceOf(AbstractJob::class, $jobMock);

        return $jobMock;
    }

    /**
     * @depends testMockConstructor
     *
     * @param AbstractJob $jobMock
     */
    public function testConstructorSetDispatcher(AbstractJob $jobMock)
    {
        $this->assertInstanceOf(EventDispatcher::class, $jobMock->getDispatcher());
    }

    /**
     * @depends testMockConstructor
     *
     * @param AbstractJob $jobMock
     */
    public function testConstructorSetParameterBag(AbstractJob $jobMock)
    {
        $this->assertInstanceOf(ParameterBag::class, $jobMock->getParameterBag());
    }

    public function testConstructor()
    {
        $jobMock = $this->generateSuccessfulJobStub();

        $this->assertInstanceOf(AbstractJob::class, $jobMock);

        return $jobMock;
    }

    /**
     * @depends testConstructor
     *
     * @param AbstractJob $jobMock
     */
    public function testGetTasksNum(AbstractJob $jobMock)
    {
        $this->assertEquals(2, $jobMock->getTasksNum());
    }

    /**
     * @depends testConstructor
     *
     * @param AbstractJob $jobMock
     */
    public function testGetClass(AbstractJob $jobMock)
    {
        $class = substr(strrchr(AbstractJob::class, '\\'), 1);
        $this->assertRegExp("/Mock_{$class}_[\w]{8}/", $jobMock->getClass());
    }

    /**
     * @depends testConstructor
     *
     * @param AbstractJob $jobMock
     *
     * @return AbstractJob
     */
    public function testRunGetCurrentStepNumBefore(AbstractJob $jobMock)
    {
        $this->assertEquals(-1, $jobMock->getCurrentTaskNum());

        return $jobMock;
    }

    /**
     * @depends testRunGetCurrentStepNumBefore
     *
     * @param AbstractJob $jobMock
     *
     * @return AbstractJob
     */
    public function testRunIsSuccessful(AbstractJob $jobMock)
    {
        $jobMock->run();

        $this->assertTrue(JobStatus::isSuccess($jobMock->getStatus()));

        return $jobMock;
    }

    /**
     * @depends testRunIsSuccessful
     *
     * @param AbstractJob $jobMock
     * @expectedException              \LogicException
     * @expectedExceptionMessageRegExp /Only clean status job can be run \[\d+\]/
     */
    public function testReRunThrowsException(AbstractJob $jobMock)
    {
        $jobMock->run();
    }

    /**
     * @depends testRunIsSuccessful
     *
     * @param AbstractJob $jobMock
     */
    public function testRunGetCurrentStepNumAfter(AbstractJob $jobMock)
    {
        $this->assertEquals(2, $jobMock->getCurrentTaskNum());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Task class must implement TaskInterface: ".+" does not/
     */
    public function testWrongTaskClassThrowsException()
    {
        $jobMock = $this->generateWrongTaskClassJobStub();
        $jobMock->run();
    }

    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}
