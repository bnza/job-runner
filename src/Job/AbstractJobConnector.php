<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

use Bnza\JobRunnerBundle\Job\Entity\JobEntityInterface;
use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;

abstract class AbstractJobConnector implements JobConnectorInterface
{
    /**
     * @var JobEntityInterface
     */
    protected $entity;

    /**
     * @var JobObjectManagerInterface
     */
    protected $om;

    public function __construct(JobObjectManagerInterface $om, string $id = '')
    {
        $this->om = $om;

        if ($id) {
            $this->entity = $this->om->findJob($id);
        }
    }

    public function getId(): string
    {
        return $this->getEntity()->getId();
    }

    public function getStatus(): int
    {
        $this->getObjectManager()->refresh($this->getEntity(), 'status');

        return $this->getEntity()->getStatus();
    }

    protected function setStatus(int $status)
    {
        $this->getEntity()->setStatus($status);
        $this->getObjectManager()->persist($this->getEntity(), 'status');
    }

    public function getClass(): string
    {
        return $this->getEntity()->getClass();
    }

    public function getCurrentTaskNum(): int
    {
        $this->getObjectManager()->refresh($this->getEntity(), 'current_task_num');

        return $this->getEntity()->getCurrentTaskNum();
    }

    public function getError(): string
    {
        $this->getObjectManager()->refresh($this->getEntity(), 'error');

        return $this->getEntity()->getError();
    }

    /**
     * Set the CANCELLED status flag true.
     */
    public function cancel()
    {
        if (!JobStatus::isRunning($this->getStatus())) {
            throw new \RuntimeException('Only running job can be cancelled');
        }
        $this->setStatus(JobStatus::cancel($this->getEntity()->getStatus()));
    }

    public function getObjectManager(): JobObjectManagerInterface
    {
        return $this->om;
    }

    protected function getEntity(): JobEntityInterface
    {
        return $this->entity;
    }
}
