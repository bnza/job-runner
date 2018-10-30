<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 03/11/18
 * Time: 8.19.
 */

namespace Bnza\JobRunnerBundle\Job;

use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;
use Bnza\JobRunnerBundle\Job\Entity\TaskEntityInterface;

abstract class AbstractTaskConnector implements AbstractTaskInterface
{
    const NO_TASK = -1;

    /**
     * @var JobObjectManagerInterface
     */
    protected $om;

    /**
     * @var TaskEntityInterface
     */
    protected $entity;

    /**
     * AbstractTaskConnector constructor.
     *
     * @param JobObjectManagerInterface $om
     * @param TaskEntityInterface       $entity
     */
    public function __construct(JobObjectManagerInterface $om, TaskEntityInterface $entity)
    {
        $this->om = $om;
        $this->entity = $entity;
    }

    protected function getEntity(): TaskEntityInterface
    {
        return $this->entity;
    }

    protected function getObjectManager()
    {
        return $this->om;
    }

    public function getClass(): string
    {
        return $this->getEntity()->getClass();
    }

    public function getError(): string
    {
        $this->getObjectManager()->refresh($this->getEntity(), 'error');

        return $this->getEntity()->getError();
    }

    public function getStepsNum(): int
    {
        $this->getObjectManager()->refresh($this->getEntity(), 'steps_num');

        return $this->getEntity()->getStepsNum();
    }

    public function getCurrentStepNum(): int
    {
        $this->getObjectManager()->refresh($this->getEntity(), 'current_step_num');

        return $this->getEntity()->getCurrentStepNum();
    }

    public function isError(): bool
    {
        return (bool) $this->getError();
    }

    public function isRunning(): bool
    {
        $currentStep = $this->getCurrentStepNum();

        return !$this->isError() && (0 < $currentStep) && ($currentStep < $this->getStepsNum());
    }
}
