<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

use Bnza\JobRunnerBundle\Exception\JobRunnerException;
use Bnza\JobRunnerBundle\Exception\JobRunnerJobCancelledException;
use Bnza\JobRunnerBundle\Job\Entity\TaskEntityInterface;
use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;

/**
 * Class AbstractTask.
 */
abstract class AbstractTask extends AbstractTaskConnector implements TaskInterface
{
    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * AbstractTask constructor.
     *
     * @param JobObjectManagerInterface $om
     * @param JobInterface              $job
     * @param int                       $num
     */
    public function __construct(JobObjectManagerInterface $om, JobInterface $job, int $num)
    {
        $this->job = $job;
        $entity = $this->generateTaskEntity($om, $num);
        parent::__construct($om, $entity);
    }

    /**
     * Generates a new TaskEntity instance and persist it. Only AbstractTask descendants SHOULD generate new task entities.
     * JobConnector (ascendants) can only retrieve existing ones.
     *
     * @param JobObjectManagerInterface $om
     * @param int                       $num
     *
     * @return TaskEntityInterface
     */
    protected function generateTaskEntity(JobObjectManagerInterface $om, int $num): TaskEntityInterface
    {
        $class = $om->getTaskEntityClass();
        $entity = $this->initTaskEntity($class, $num);
        $entity->setName($this->getName());
        $entity->setClass(get_class($this));
        $om->persist($entity);

        return $entity;
    }

    protected function initTaskEntity(string $class, int $num): TaskEntityInterface
    {
        if (in_array(TaskEntityInterface::class, class_implements($class))) {
            $entity = new $class($this->job->getId(), $num);

            return $entity;
        }
        throw new \InvalidArgumentException("Task entity class must implement TaskEntityInterface: \"$class\" does not");
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting, so let it fall
            // through to the standard PHP error handler
            return false;
        }

        throw new JobRunnerException($errstr, $errno);
    }

    /**
     * @throws JobRunnerException
     */
    final public function execute()
    {
        $callable = $this->getCallable();
        $data = $this->getData();
        try {
            foreach ($data as $num => $datum) {
                if (JobStatus::isCancelled($this->getJob()->getStatus())) {
                    throw new JobRunnerJobCancelledException();
                }
                $this->setCurrentStepNum($num);
                $datum = is_array($datum) ? $datum : [$datum];
                call_user_func_array($callable, $datum);
            }
        } catch (\Throwable $e) {
            $this->setError($e);
            if ($e instanceof JobRunnerException) {
                throw $e;
            }
            throw new JobRunnerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Sets the task current step. Generator's and array's indexes are 0 based, task's steps are 1 based. So if $oneBaseIndex
     * is set false the $num value is incremented by one.
     *
     * @param int  $num          the job current index
     * @param bool $oneBaseIndex whether the given index is 1 based or not
     */
    protected function setCurrentStepNum(int $num, bool $oneBaseIndex = true)
    {
        $this->getEntity()->setCurrentStepNum($oneBaseIndex ? ++$num : $num);
        $this->getObjectManager()->persist($this->getEntity(), 'current_step_num');
    }

    protected function setError(\Exception $e)
    {
        $this->getEntity()->setError((string) $e);
        $this->getObjectManager()->persist($this->getEntity(), 'error');
    }

    public function getJob(): JobInterface
    {
        return $this->job;
    }
}
