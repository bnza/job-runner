<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;

interface JobConnectorInterface
{
    public function getName(): string;

    public function getId(): string;

    public function getStatus(): int;

    public function getClass(): string;

    public function getTasksNum(): int;

    public function getCurrentTaskNum(): int;

    public function getError(): string;

    public function cancel();

    public function getObjectManager(): JobObjectManagerInterface;
}
