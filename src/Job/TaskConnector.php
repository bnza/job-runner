<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;

/**
 * Task connector utility class.
 *
 * @see JobConnector
 * Class TaskConnector
 */
class TaskConnector extends AbstractTaskConnector
{
    /**
     * @var JobConnectorInterface
     */
    protected $job;

    public function __construct(JobObjectManagerInterface $om, JobConnectorInterface $job, int $num)
    {
        $this->job = $job;
        $entity = $om->findTask($this->job->getId(), $num);
        parent::__construct($om, $entity);
    }

    public function getJob(): JobConnectorInterface
    {
        return $this->job;
    }

    public function getName(): string
    {
        return $this->getEntity()->getName();
    }
}
