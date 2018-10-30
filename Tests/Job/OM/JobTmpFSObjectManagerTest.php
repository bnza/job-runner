<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Tests\Job\OM;

use Bnza\JobRunnerBundle\Job\Entity\TmpFS\TaskEntity;
use Bnza\JobRunnerBundle\Tests\Job\TmpFSTestFixturesTrait;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Doctrine\Common\Inflector\Inflector;

class JobTmpFSObjectManagerTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSTestFixturesTrait;

    public function testGetPathsTmpKey()
    {
        $this->om = new JobTmpFSObjectManager();

        $dir = $this->om->getPaths('tmp');
        $this->assertEquals(sys_get_temp_dir(), $dir);
        $this->assertFileExists($dir);
    }

    public function testGetPathsBaseKey()
    {
        $this->om = new JobTmpFSObjectManager();

        $dir = $this->om->getPaths('base');
        $this->assertStringEndsWith(JobTmpFSObjectManager::BASE_DIR_NAME, $dir);
    }

    public function testGetPathsEnvKey()
    {
        $this->om = new JobTmpFSObjectManager();

        $dir = $this->om->getPaths('env');
        $this->assertStringEndsWith('test', $dir);
        $this->assertFileNotExists($dir);
    }

    public function testGetPathsJobsKey()
    {
        $this->om = new JobTmpFSObjectManager();

        $dir = $this->om->getPaths('jobs');
        $this->assertStringEndsWith('jobs', $dir);
        $this->assertFileNotExists($dir);
    }

    public function testGetPaths()
    {
        $this->om = new JobTmpFSObjectManager();

        $paths = $this->om->getPaths();
        $this->assertTrue(is_array($paths));
        $this->assertEquals(4, count($paths));
    }

    public function testPersistJobNoTask()
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();

        $this->om->persist($entity);

        $jobsDir = $this->om->getPaths('jobs');
        $jobDir = $jobsDir.DIRECTORY_SEPARATOR.$entity->getId();
        $statusFile = $jobDir.DIRECTORY_SEPARATOR.'status';

        $this->assertFileExists($jobDir);
        $this->assertFileExists($statusFile);
        $this->assertStringEqualsFile($statusFile, '0');
    }

    public function testPersistJobWithTask()
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();
        $num = 0;
        $task = new TaskEntity($entity, 0);
        $entity->addTask($task);
        $this->om->persist($entity);

        $jobsDir = $this->om->getPaths('jobs');
        $taskDir = $jobsDir
            .DIRECTORY_SEPARATOR
            .$entity->getId()
            .DIRECTORY_SEPARATOR
            .JobTmpFSObjectManager::TASKS_DIR_NAME
            .DIRECTORY_SEPARATOR
            .$num
        ;

        $this->assertFileExists($taskDir);
    }

    public function jobPropertiesProvider()
    {
        return [
            ['status', 23],
            ['class', self::class],
            ['name', 'Job name'],
            ['current_task_num', 4],
        ];
    }

    /**
     * @dataProvider jobPropertiesProvider
     */
    public function testPersistJobProperty($prop, $value)
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();
        $inflector = new Inflector();
        $method = 'set'.$inflector->classify($prop);
        $entity->$method($value);

        $this->om->persist($entity, $prop);

        $jobsDir = $this->om->getPaths('jobs');
        $jobDir = $jobsDir.DIRECTORY_SEPARATOR.$entity->getId();
        $propFile = $jobDir.DIRECTORY_SEPARATOR.$prop;

        $this->assertFileExists($jobDir);
        $this->assertFileExists($propFile);
        $this->assertStringEqualsFile($propFile, $value);
    }

    public function testRefreshJob()
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();

        $this->om->persist($entity);

        $jobsDir = $this->om->getPaths('jobs');
        $jobDir = $jobsDir.DIRECTORY_SEPARATOR.$entity->getId();
        $statusFile = $jobDir.DIRECTORY_SEPARATOR.'status';

        file_put_contents($statusFile, '1');

        $this->om->refresh($entity);

        $this->assertEquals(1, $entity->getStatus());
    }

    /**
     * @dataProvider jobPropertiesProvider
     */
    public function testRefreshJobProp($prop, $value)
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();

        $this->om->persist($entity);

        $jobsDir = $this->om->getPaths('jobs');
        $jobDir = $jobsDir.DIRECTORY_SEPARATOR.$entity->getId();
        $statusFile = $jobDir.DIRECTORY_SEPARATOR.$prop;

        file_put_contents($statusFile, $value);

        $this->om->refresh($entity, $prop);

        $inflector = new Inflector();
        $method = 'get'.$inflector->classify($prop);

        $this->assertEquals($value, $entity->$method());
    }

    public function testFindValidId()
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();

        $this->om->persist($entity);

        $foundEntity = $this->om->findJob($entity->getId());

        $this->assertInstanceOf(JobEntity::class, $foundEntity);
        $this->assertEquals($entity, $foundEntity);
    }

    /**
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /".+" is not a valid sha1 hash/
     */
    public function testFindInvalidId()
    {
        $this->om = new JobTmpFSObjectManager();
        $sha1 = 'Not sha1 hash';
        $entity = $this->om->findJob($sha1);
    }

    /**
     * @expectedException              \Bnza\JobRunnerBundle\Exception\JobRunnerEntityNotFoundException
     * @expectedExceptionMessageRegExp /".+" job not found/
     */
    public function testFindNotFoundId()
    {
        $this->om = new JobTmpFSObjectManager();

        $foundEntity = $this->om->findJob('d54eff505dadd03c2dcdd71619562fdf449bd74e');
    }

    public function testPersistTask()
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();
        $num = 23;
        $task = new TaskEntity($entity, $num);
        $this->om->persist($task);

        $jobsDir = $this->om->getPaths('jobs');
        $taskDir = $jobsDir
            .DIRECTORY_SEPARATOR
            .$entity->getId()
            .DIRECTORY_SEPARATOR
            .JobTmpFSObjectManager::TASKS_DIR_NAME
            .DIRECTORY_SEPARATOR
            .$num
        ;

        $this->assertFileExists($taskDir);
    }

    public function taskPropertiesProvider()
    {
        return [
            ['class', self::class],
            ['current_step_num', 11]
        ];
    }

    /**
     * @dataProvider taskPropertiesProvider
     */
    public function testPersistTaskProperty($prop, $value)
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();
        $num = 23;
        $task = new TaskEntity($entity, $num);
        $inflector = new Inflector();
        $method = 'set'.$inflector->classify($prop);
        $task->$method($value);

        $this->om->persist($task);

        $jobsDir = $this->om->getPaths('jobs');
        $taskDir = $jobsDir
            .DIRECTORY_SEPARATOR
            .$entity->getId()
            .DIRECTORY_SEPARATOR
            .JobTmpFSObjectManager::TASKS_DIR_NAME
            .DIRECTORY_SEPARATOR
            .$num
        ;

        $propFile = $taskDir.DIRECTORY_SEPARATOR.$prop;

        $this->assertStringEqualsFile($propFile, $value);
    }

    public function testRefreshTask()
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();
        $num = 23;
        $task = new TaskEntity($entity, $num);

        $this->om->persist($task);

        $jobsDir = $this->om->getPaths('jobs');
        $taskDir = $jobsDir
            .DIRECTORY_SEPARATOR
            .$entity->getId()
            .DIRECTORY_SEPARATOR
            .JobTmpFSObjectManager::TASKS_DIR_NAME
            .DIRECTORY_SEPARATOR
            .$num
        ;
        $statusFile = $taskDir.DIRECTORY_SEPARATOR.'current_step_num';

        $stepNum = 45;
        file_put_contents($statusFile, $stepNum);

        $this->om->refresh($task);

        $this->assertEquals($stepNum, $task->getCurrentStepNum());
    }

    /**
     * @dataProvider taskPropertiesProvider
     */
    public function testRefreshTaskProp($prop, $value)
    {
        $this->om = new JobTmpFSObjectManager();
        $entity = new JobEntity();
        $num = 23;
        $task = new TaskEntity($entity, $num);

        $this->om->persist($task);

        $jobsDir = $this->om->getPaths('jobs');
        $taskDir = $jobsDir
            .DIRECTORY_SEPARATOR
            .$entity->getId()
            .DIRECTORY_SEPARATOR
            .JobTmpFSObjectManager::TASKS_DIR_NAME
            .DIRECTORY_SEPARATOR
            .$num
        ;
        $propFile = $taskDir.DIRECTORY_SEPARATOR.$prop;

        file_put_contents($propFile, $value);

        $this->om->refresh($task, $prop);

        $inflector = new Inflector();
        $method = 'get'.$inflector->classify($prop);

        $this->assertEquals($value, $task->$method());
    }

    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}
