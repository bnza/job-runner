<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bnza\JobRunnerBundle\Job;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobManager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var OM\JobObjectManagerInterface
     */
    private $om;

    public function __construct(EventDispatcherInterface $dispatcher, OM\JobObjectManagerInterface $om)
    {
        $this->dispatcher = $dispatcher;
        $this->om = $om;
    }

}