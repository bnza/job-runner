<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */


namespace Bnza\JobRunnerBundle\Tests\Job;

use Symfony\Component\Filesystem\Filesystem;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;

trait TmpFSTestFixturesTrait
{
    /**
     * @var JobTmpFSObjectManager
     */
    private $om;

    public function removeTestJobDirectory()
    {
        if ($this->om) {
            //Remove env work dir (test)
            $dir = $this->om->getPaths('env');
            if (file_exists($dir)) {
                $fs = new Filesystem();
                $fs->remove($dir);
            }
        }
    }
}