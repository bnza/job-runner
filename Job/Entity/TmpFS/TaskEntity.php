<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bnza\JobRunnerBundle\Job\Entity\TmpFS;

use Bnza\JobRunnerBundle\Job\Entity\JobEntityInterface;
use Bnza\JobRunnerBundle\Job\Entity\TaskEntityInterface;

class TaskEntity extends AbstractJobRunnerEntity implements TaskEntityInterface
{
    /**
     * @var JobEntity
     */
    private $job;

    /**
     * @var int
     */
    private $num;

    /**
     * @var int
     */
    private $currentStepNum = 0;

    public function __construct($job, int $num = -1)
    {
        if ($job) {
            if ($job instanceof JobEntity) {
                $this->job = $job;
            } else if (is_string($job)) {
                $this->job = new JobEntity($job);
            } else {
                throw new \InvalidArgumentException("Invalid job given");
            }
        }
        if ($num != -1) {
            $this->num = $num;
        }
    }

    public function getJob(): JobEntityInterface
    {
        return $this->job;
    }

    public function setJob(JobEntityInterface $job)
    {
        $this->job = $job;
    }

    public function getNum(): int
    {
        return $this->num;
    }

    public function setNum($num)
    {
        $this->num = (int) $num;
    }

    public function setCurrentStepNum($num)
    {
        $this->currentStepNum = (int) $num;
    }

    public function getCurrentStepNum(): int
    {
        return $this->currentStepNum;
    }
}