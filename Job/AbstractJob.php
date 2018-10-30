<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;


use Bnza\JobRunnerBundle\Job\Event\JobEvent;
use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractJob extends JobConnector implements JobInterface
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

        foreach ($tasks as $taskData) {
            $task = $this->initTask($taskData);
            $task->execute();
        }

        $this->setSuccess();
    }

    /**
     * @param array $taskData
     *
     * @return TaskInterface
     */
    protected function initTask(array $taskData): TaskInterface
    {
        $class = $taskData['class'];
        $this->setCurrentTaskNum($this->entity->getCurrentTaskNum() + 1);
        $task = new $class($this->om, $this, $this->entity->getCurrentTaskNum());

        return $task;
    }

    protected function setRunning($flag)
    {
        $status = $this->getStatus();
        if ($flag) {
            if (0 !== $status) {
                throw new \LogicException(sprintf('Only clean status job can be run [%b]', $this->entity->getStatus()));
            }
            $eventName = JobEvent::STARTED;
        } else {
            if (!JobStatus::isRunning($status)) {
                throw new \LogicException("AbstractJob's is not running yet");
            }
        }
        $this->setStatus(JobStatus::setStatus($status, JobStatus::RUNNING, $flag));

        if (isset($eventName)) {
            $this->dispatcher->dispatch($eventName, $this->jobEvent);
        }
    }

    protected function setSuccess()
    {
        $status = $this->getStatus();

        if (JobStatus::isError($status)) {
            throw new \LogicException(sprintf('Cannot set success flag on error flagged job [%b]', $this->entity->getStatus()));
        }

        $this->setStatus(JobStatus::success($this->getStatus()));
    }

    protected function generateJobEntity()
    {
        $class = $this->om->getJobEntityClass();
        $this->entity = new $class();
        $this->entity->setClass(get_class($this));
        $this->om->persist($this->entity);
    }
}
