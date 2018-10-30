<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bnza\JobRunnerBundle\Job\Entity;


interface TaskEntityInterface extends JobRunnerEntityInterface
{
    public function getJob(): JobEntityInterface;

    public function setJob(JobEntityInterface $job);

    public function setNum($num);

    public function getNum(): int;

    public function setCurrentStepNum($num);

    public function getCurrentStepNum(): int;


}