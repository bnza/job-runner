<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bnza\JobRunnerBundle\Job\OM;

use Bnza\JobRunnerBundle\Exception\JobRunnerJobEntityNotFoundException;
use Bnza\JobRunnerBundle\Exception\JobRunnerTaskEntityNotFoundException;
use Bnza\JobRunnerBundle\Job\Entity\JobRunnerEntityInterface;
use Bnza\JobRunnerBundle\Job\Entity\JobEntityInterface;
use Bnza\JobRunnerBundle\Job\Entity\TaskEntityInterface;

interface JobObjectManagerInterface
{
    public function persist(JobRunnerEntityInterface $entity, string $property = '');

    public function refresh(JobRunnerEntityInterface $entity, string $property = '');

    /**
     * @param string $id
     * @return JobEntityInterface
     * @throws JobRunnerJobEntityNotFoundException
     */
    public function findJob(string $id): JobEntityInterface;

    /**
     * @param string $jobId
     * @param int $num
     * @return TaskEntityInterface
     * @throws JobRunnerTaskEntityNotFoundException;
     */
    public function findTask(string $jobId, int $num): TaskEntityInterface;

    /**
     * Return the right JobEntityClass used by the ObjectManager
     * @return string
     */
    public function getJobEntityClass(): string;

    /**
     * Return the right TaskEntityClass used by the ObjectManager
     * @return string
     */
    public function getTaskEntityClass(): string;
}