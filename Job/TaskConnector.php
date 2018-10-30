<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bnza\JobRunnerBundle\Job;

use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;
use Bnza\JobRunnerBundle\Job\Entity\TaskEntityInterface;

class TaskConnector implements TaskConnectorInterface
{
    const NO_TASK = -1;

    /**
     * @var JobConnectorInterface
     */
    protected $job;

    /**
     * @var JobObjectManagerInterface
     */
    protected $om;

    /**
     * @var TaskEntityInterface
     */
    protected $entity;

    public function __construct(JobObjectManagerInterface $om, JobConnectorInterface $job, int $num = self::NO_TASK)
    {
        $this->om = $om;
        $this->job = $job;
        if ($num != self::NO_TASK) {
            $this->entity = $this->om->findTask($this->job->getId(), $num);
        }
    }
}