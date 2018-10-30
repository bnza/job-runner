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
 * Date: 30/10/18
 * Time: 8.55.
 */

namespace Bnza\JobRunnerBundle\Job;

interface TaskConnectorInterface extends AbstractTaskInterface
{
    public function getJob(): JobConnectorInterface;
}
