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
 * Date: 29/10/18
 * Time: 16.09.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\JobStatus;

class JobStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testIsRunningFalse()
    {
        $this->assertFalse(JobStatus::isRunning(0b0000));
    }

    public function testIsRunningTrue()
    {
        $this->assertTrue(JobStatus::isRunning(JobStatus::RUNNING));
    }

    public function testIsSuccessFalse()
    {
        $this->assertFalse(JobStatus::isSuccess(0b0000));
    }

    public function testIsSuccessTrue()
    {
        $this->assertTrue(JobStatus::isSuccess(JobStatus::SUCCESS));
    }

    public function testIsCancelledFalse()
    {
        $this->assertFalse(JobStatus::isCancelled(0b0000));
    }

    public function testIsCancelledTrue()
    {
        $this->assertTrue(JobStatus::isCancelled(JobStatus::CANCELLED));
    }

    public function testIsErrorFalse()
    {
        $this->assertFalse(JobStatus::isError(0b0000));
    }

    public function testIsErrorTrue()
    {
        $this->assertTrue(JobStatus::isError(JobStatus::ERROR));
    }

    public function jobStatusProvider()
    {
        return [
            [JobStatus::RUNNING],
            [JobStatus::SUCCESS],
            [JobStatus::CANCELLED],
            [JobStatus::ERROR],
        ];
    }

    /**
     * @dataProvider jobStatusProvider
     *
     * @param $bitMask
     */
    public function testSetStatusOn($bitMask)
    {
        $status = JobStatus::setStatus(0b0000, $bitMask, true);
        $this->assertTrue((bool) ($status & $bitMask));
    }

    /**
     * @dataProvider jobStatusProvider
     *
     * @param $bitMask
     */
    public function testSetStatusOff($bitMask)
    {
        $status = JobStatus::setStatus(0b1111, $bitMask, false);
        $this->assertFalse((bool) ($status & $bitMask));
    }

    public function testCancel()
    {
        $status = JobStatus::cancel(0b0001);
        $this->assertTrue((bool) ($status & JobStatus::CANCELLED));
        $this->assertFalse((bool) ($status & JobStatus::RUNNING));
    }

    public function testSuccess()
    {
        $status = JobStatus::success(0b0001);
        $this->assertTrue((bool) ($status & JobStatus::SUCCESS));
        $this->assertFalse((bool) ($status & JobStatus::RUNNING));
    }

    public function testError()
    {
        $status = JobStatus::error(0b0001);
        $this->assertTrue((bool) ($status & JobStatus::ERROR));
    }
}
