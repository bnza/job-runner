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

abstract class AbstractTask extends TaskConnector
{
    /**
     * @var JobInterface
     */
    protected $job;

    public function __construct(JobObjectManagerInterface $om, JobInterface $job, int $num)
    {
        parent::__construct($om, $job, TaskConnector::NO_TASK);
    }

    protected function generateTaskEntity(int $num)
    {
        $class = $this->om->getTaskEntityClass();
        $this->entity = new $class($this->job->getId(), $num);
        $this->entity->setClass(get_class($this));
        $this->om->persist($this->entity);
    }
}