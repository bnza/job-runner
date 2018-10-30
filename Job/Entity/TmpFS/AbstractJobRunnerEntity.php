<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job\Entity\TmpFS;

use Bnza\JobRunnerBundle\Job\Entity\JobRunnerEntityInterface;

abstract class AbstractJobRunnerEntity implements JobRunnerEntityInterface
{
    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    private $name = '';

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class)
    {
        $this->class = $class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
