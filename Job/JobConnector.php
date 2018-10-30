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

use Bnza\JobRunnerBundle\Job\Entity\JobEntityInterface;
use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;

class JobConnector implements JobConnectorInterface
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
        return $this->entity->getId();
    }

    public function getStatus(): int
    {
        $this->om->refresh($this->entity, 'status');
        return $this->entity->getStatus();
    }

    protected function setStatus(int $status)
    {
        $this->entity->setStatus($status);
        $this->om->persist($this->entity, 'status');
    }

    public function getClass(): string
    {
        return $this->entity->getClass();
    }

    public function getCurrentTaskNum(): int
    {
        $this->om->refresh($this->entity, 'current_task_num');
    }

    public function setCurrentTaskNum(int $num)
    {
        $this->entity->setCurrentTaskNum($num);
        $this->om->persist($this->entity, 'current_task_num');
    }

    public function getName(): string
    {
        return $this->entity->getName();
    }

    /**
     * Set the CANCELLED status flag true.
     */
    public function cancel()
    {
        $this->setStatus(JobStatus::cancel($this->getStatus()));
    }
}