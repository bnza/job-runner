<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

/**
 * Job connector utility class
 * Utility class used for simple inter-process communication achieved by data persisting (DB or FS).
 * It retrieves data about running (or run) job using the data persisting layer (JobObjectManagerInterface)
 * It could also force running job abort.
 *
 * Class JobConnector
 */
class JobConnector extends AbstractJobConnector
{
    public function getName(): string
    {
        return $this->getEntity()->getName();
    }

    public function getTasksNum(): int
    {
        return $this->getEntity()->getTasksNum();
    }
}
