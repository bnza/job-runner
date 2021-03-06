<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job;

interface AbstractTaskInterface
{
    public function getStepsNum(): int;

    public function getCurrentStepNum(): int;

    public function getClass(): string;

    public function getName(): string;

    public function getError(): string;

    public function isError(): bool;

    public function isRunning(): bool;
}
