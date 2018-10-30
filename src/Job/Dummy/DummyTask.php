<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job\Dummy;

use Bnza\JobRunnerBundle\Job\AbstractTask;

class DummyTask extends AbstractTask
{
    public function getName(): string
    {
        return 'Dummy task name';
    }

    public function taskCallable()
    {
    }

    public function getData(): iterable
    {
        return [];
    }

    public function getCallable(): callable
    {
        return [$this, 'taskCallable'];
    }
}
