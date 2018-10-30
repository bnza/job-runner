<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bnza\JobRunnerBundle\Job\Event;

use Bnza\JobRunnerBundle\Job\TaskInterface;
use Symfony\Component\EventDispatcher\Event;

class TaskEvent extends Event
{
    public const CREATED = 'bnza.job_runner.task.created';
}