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

    /**
     * @var string
     */
    protected $error = '';

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): JobRunnerEntityInterface
    {
        $this->class = $class;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): JobRunnerEntityInterface
    {
        $this->name = $name;

        return $this;
    }

    public function setError(string $error): JobRunnerEntityInterface
    {
        $this->error = $error;

        return $this;
    }

    public function getError(): string
    {
        return $this->error;
    }
}
