<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */


namespace Bnza\JobRunnerBundle\Exception;


class JobRunnerTaskEntityNotFoundException extends JobRunnerEntityNotFoundException
{
    /**
     * JobRunnerJobEntityNotFoundException constructor.
     * @param string $message The not found job id
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $message = "\"$message\" task not found";
        parent::__construct($message, $code, $previous);
    }
}