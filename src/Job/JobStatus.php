<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

final class JobStatus
{
    const RUNNING = 0b0001;
    const SUCCESS = 0b0010;
    const CANCELLED = 0b0100;
    const ERROR = 0b1000;

    /**
     * @param int $status
     *
     * @return bool
     */
    public static function isRunning(int $status): bool
    {
        return (bool) ($status & self::RUNNING);
    }

    /**
     * @param int $status
     *
     * @return bool
     */
    public static function isSuccess(int $status)
    {
        return (bool) ($status & self::SUCCESS);
    }

    /**
     * @param int $status
     *
     * @return bool
     */
    public static function isCancelled(int $status)
    {
        return (bool) ($status & self::CANCELLED);
    }

    /**
     * @param int $status
     *
     * @return bool
     */
    public static function isError(int $status)
    {
        return (bool) ($status & JobStatus::ERROR);
    }

    /**
     * Set the bit mask on/off (depending on $flag) on the given $status.
     *
     * @param int  $status
     * @param int  $bitMask
     * @param bool $flag
     *
     * @return int
     */
    public static function setStatus(int $status, int $bitMask, bool $flag): int
    {
        if ($flag) {
            return $status | $bitMask;
        } else {
            return $status & ~$bitMask;
        }
    }

    public static function cancel(int $status)
    {
        return $status & ~self::RUNNING | self::CANCELLED;
    }

    public static function success(int $status)
    {
        return $status & ~self::RUNNING | self::SUCCESS;
    }

    public static function error(int $status)
    {
        return $status | self::ERROR;
    }
}
