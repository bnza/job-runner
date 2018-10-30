<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job\Event;

use Bnza\JobRunnerBundle\Job\JobInterface;
use Symfony\Component\EventDispatcher\Event;

class JobEvent extends Event
{
    public const STARTED = 'bnza.job_runner.job.started';
    public const TERMINATED = 'bnza.job_runner.job.terminated';

    /**
     * @var JobInterface
     */
    protected $job;

    public function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * @return JobInterface
     */
    public function getJob(): JobInterface
    {
        return $this->job;
    }
}
