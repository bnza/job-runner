<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface JobInterface extends JobConnectorInterface
{
    public function getParameterBag(): ParameterBag;

    public function getTasks(): iterable;

    public function getDispatcher(): EventDispatcherInterface;

    public function run(): void;

    public function configure(): void;
}
