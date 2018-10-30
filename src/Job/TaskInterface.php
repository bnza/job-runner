<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

interface TaskInterface extends AbstractTaskInterface
{
    public function execute();

    public function getData(): iterable;

    public function getCallable(): callable;

    public function getJob(): JobInterface;
}
