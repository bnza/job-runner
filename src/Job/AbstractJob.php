<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

use Bnza\JobRunnerBundle\Exception\JobRunnerJobCancelledException;
use Bnza\JobRunnerBundle\Job\Entity\JobEntityInterface;
use Bnza\JobRunnerBundle\Job\Event\JobEvent;
use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractJob extends AbstractJobConnector implements JobInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ParameterBag
     */
    protected $pb;

    /**
     * @var JobEvent
     */
    protected $jobEvent;

    public function __construct(EventDispatcherInterface $dispatcher, JobObjectManagerInterface $om, ParameterBag $pb)
    {
        parent::__construct($om, '');
        $this->generateJobEntity();
        $this->dispatcher = $dispatcher;
        $this->pb = $pb;
    }

    public function getParameterBag(): ParameterBag
    {
        return $this->pb;
    }

    final public function run(): void
    {
        $this->jobEvent = new JobEvent($this);

        $this->setRunning(true);

        $tasks = $this->getTasks();

        foreach ($tasks as $num => $taskData) {
            if (JobStatus::isCancelled($this->getStatus())) {
                throw new JobRunnerJobCancelledException();
            }
            $task = $this->initTask($num, $taskData);
            $task->execute();
        }

        $this->setSuccess();
    }

    /**
     * Initializes a new TaskInterface instance using $taskData array which is in the form
     * array(3) {
     *  [0]=>
     *  string(*) The fully qualified Task class name (MUST implements TaskInterface)
     *}.
     *
     * @param int   $num
     * @param array $taskData
     *
     * @return TaskInterface
     */
    protected function initTask(int $num, array $taskData): TaskInterface
    {
        $class = $taskData[0];
        if (in_array(TaskInterface::class, class_implements($class))) {
            $this->setCurrentTaskNum($num);
            $task = new $class($this->getObjectManager(), $this, $num);

            return $task;
        }
        throw new \InvalidArgumentException("Task class must implement TaskInterface: \"$class\" does not");
    }

    /**
     * Sets the job RUNNING flag on/off depending on $flag.
     *
     * @param $flag
     */
    protected function setRunning($flag)
    {
        $status = $this->getStatus();
        if ($flag) {
            if (0 !== $status) {
                throw new \LogicException(sprintf('Only clean status job can be run [%b]', $this->getEntity()->getStatus()));
            }
            $eventName = JobEvent::STARTED;
        } else {
            if (!JobStatus::isRunning($status)) {
                throw new \LogicException("AbstractJob's is not running yet");
            }
        }
        $this->setStatus(JobStatus::setStatus($status, JobStatus::RUNNING, $flag));

        if (isset($eventName)) {
            $this->getDispatcher()->dispatch($eventName, $this->jobEvent);
        }
    }

    /**
     * Sets the job SUCCESS flag on.
     *
     * @param
     */
    protected function setSuccess()
    {
        $status = $this->getStatus();

        if (JobStatus::isError($status)) {
            throw new \LogicException(sprintf('Cannot set success flag on error flagged job [%b]', $this->getEntity()->getStatus()));
        }

        $this->setStatus(JobStatus::success($this->getStatus()));
    }

    /**
     * Generates a new JobEntity instance and persist it. Only AbstractJob descendants SHOULD generate new job entities.
     * JobConnector (ascendants) can only retrieve existing ones.
     */
    protected function generateJobEntity()
    {
        $class = $this->getObjectManager()->getJobEntityClass();
        $this->entity = new $class();
        $this->getEntity()
            ->setName($this->getName())
            ->setClass(get_class($this));
        // First time task count
        $this->getTasksNum();
        $this->getObjectManager()->persist($this->getEntity());
    }

    /**
     * Sets the job current index. Generator's and array's indexes are 0 based, job's task's indexes are 1 based. So if $oneBaseIndex
     * is set false the $num vale is incremented by one.
     *
     * @param int  $num          the job current index
     * @param bool $oneBaseIndex whether the given index is 1 based or not
     */
    private function setCurrentTaskNum(int $num, bool $oneBaseIndex = true)
    {
        $this->getEntity()->setCurrentTaskNum($oneBaseIndex ? ++$num : $num);
        $this->getObjectManager()->persist($this->getEntity(), 'current_task_num');
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * Count tasks entry to determine tasks number.
     *
     * @return int
     */
    public function getTasksNum(): int
    {
        if (-1 == $this->getEntity()->getTasksNum()) {
            $num = count(iterator_to_array($this->getTasks()));
            $this->getEntity()->setTasksNum($num);
        }

        return $this->getEntity()->getTasksNum();
    }

    protected function getEvent(): JobEvent
    {
        if (!$this->jobEvent) {
            $this->jobEvent = new JobEvent($this);
        }

        return $this->jobEvent;
    }

    protected function getEntity(): JobEntityInterface
    {
        if (!$this->entity) {
            $this->generateJobEntity();
        }

        return parent::getEntity();
    }
}
