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
 * Date: 02/11/18
 * Time: 10.32.
 */

namespace Bnza\JobRunnerBundle\Tests\Job\OM;

use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;

class JobTmpFSObjectManagerTestTwo extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $om = new JobTmpFSObjectManager();
//        $this->om = clone $om;
        $this->assertInstanceOf(JobTmpFSObjectManager::class, $om);

        return $om;
    }

    public function sha1Provider()
    {
        yield  [sha1('A')];
    }

    /**
     * @dataProvider sha1Provider
     * @depends      testConstructor
     */
    public function testGetJobWorkDir(string $sha1, JobTmpFSObjectManager $om)
    {
        //$path = $om->getJobWorkDir($sha1);
        //$this->assertEquals($om->getPaths('jobs').DIRECTORY_SEPARATOR.$sha1, $path);
        $this->assertTrue((bool) $om);

        return $om;
    }

    /**
     * @depends      testGetJobWorkDir
     */
    public function testGetTasksWorkDir(JobTmpFSObjectManager $om)
    {
        $this->assertEquals($om->getJobWorkDir($sha1).DIRECTORY_SEPARATOR.JobTmpFSObjectManager::TASKS_DIR_NAME, $path);

        return $om;
    }
}
