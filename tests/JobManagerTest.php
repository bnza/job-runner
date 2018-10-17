<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunner\Tests;

use Bnza\JobRunner\JobManager;
use Symfony\Component\Filesystem\Filesystem;

class JobManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var JobManager
     */
    private $jobManager;

    public function setUp()
    {
        $this->jobManager = new JobManager();
    }

    /**
     * @expectedException              \RuntimeException
     * @expectedExceptionMessageRegExp /Unable to create .+ directory/
     *
     * @throws \ReflectionException
     */
    public function testCreateDirectoryException()
    {
        $ref = new \ReflectionClass('Bnza\JobRunner\JobManager');

        $method = $ref->getMethod('createDirectory');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->jobManager, '/not-existent/directory'));
        $this->assertTrue($method->invoke($this->jobManager, '/root/unpermitted-directory'));
    }

    public function testGetBaseWorkDir()
    {
        $dir = $this->jobManager->getBaseWorkDir();
        $this->assertFileExists($dir);
    }

    public function testGetBaseWorkDirNotCreating()
    {
        $dir = $this->jobManager->getBaseWorkDir(false);
        $this->assertFalse(file_exists($dir));
    }

    public function testGetJobsWorkDir()
    {
        $dir = $this->jobManager->getJobsWorkDir();
        $this->assertFileExists($dir);
    }

    public function testGetJobsWorkDirNotCreating()
    {
        $dir = $this->jobManager->getJobsWorkDir(false);
        $this->assertFalse(file_exists($dir));
    }

    public function tearDown()
    {
        //Remove work dir
        $dir = $this->jobManager->getBaseWorkDir(false);
        if (file_exists($dir)) {
            $fs = new Filesystem();
            $fs->remove($dir);
        }
    }
}
