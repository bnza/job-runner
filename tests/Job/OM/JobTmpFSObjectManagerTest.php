<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Tests\Job\OM;

use Bnza\JobRunnerBundle\Job\Entity\TmpFS\TaskEntity;
use Bnza\JobRunnerBundle\Tests\Job\TmpFSFixturesTrait;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Doctrine\Common\Inflector\Inflector;

class JobTmpFSObjectManagerTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSFixturesTrait;

    /**
     * @var string
     */
    const JOB_ID = '86f7e437faa5a7fce15d1ddcb9eaeaea377667b8';

    /**
     * @var int
     */
    const TASK_NUM = 0;

    public function setUp()
    {
        $this->setUpOm();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(JobTmpFSObjectManager::class, $this->om);

        return $this->om;
    }

    public function omPathKeysProvider()
    {
        return [
            ['tmp', sys_get_temp_dir()],
            ['base', JobTmpFSObjectManager::BASE_DIR_NAME],
            ['env', 'test'],
            ['jobs', JobTmpFSObjectManager::JOBS_DIR_NAME],
        ];
    }

    /**
     * @dataProvider omPathKeysProvider
     * @depends      testConstructor
     *
     * @param string                $key
     * @param string                $path
     * @param JobTmpFSObjectManager $om
     */
    public function testGetPathsKeys(string $key, string $path, JobTmpFSObjectManager $om)
    {
        $dir = $om->getPaths($key);

        if (DIRECTORY_SEPARATOR == $path[0]) {
            $this->assertEquals($path, $dir);
        } else {
            $this->assertStringEndsWith($path, $dir);
        }
    }

    /**
     * @depends      testConstructor
     *
     * @param JobTmpFSObjectManager $om
     */
    public function testGetPaths(JobTmpFSObjectManager $om)
    {
        $paths = $om->getPaths();
        $this->assertCount(4, $paths);
    }

//    /**
//     * @depends      testConstructor
//     * @param JobTmpFSObjectManager $om
//     */
//    public function testPersistJobWithTask(JobTmpFSObjectManager $om)
//    {
//        $entity = new JobEntity();
//        $num = 0;
//        $task = new TaskEntity($entity, $num);
//        $entity->addTask($task);
//        $om->persist($entity);
//
//        $jobsDir = $om->getPaths('jobs');
//        $taskDir = $jobsDir
//            .DIRECTORY_SEPARATOR
//            .$entity->getId()
//            .DIRECTORY_SEPARATOR
//            .JobTmpFSObjectManager::TASKS_DIR_NAME
//            .DIRECTORY_SEPARATOR
//            .$num
//        ;
//
//        $this->assertFileExists($taskDir);
//    }

    public function jobPropertiesProvider()
    {
        return [
            ['status', 23],
            ['class', self::class],
            ['name', 'Job name'],
            ['current_task_num', 4],
        ];
    }

//    /**
//     * @dataProvider jobPropertiesProvider
//     * @depends      testConstructor
//     * @param $prop
//     * @param $value
//     * @param JobTmpFSObjectManager $om
//     */
//    public function testPersistJobProperty($prop, $value, JobTmpFSObjectManager $om)
//    {
//        $entity = new JobEntity();
//        $inflector = new Inflector();
//        $method = 'set'.$inflector->classify($prop);
//        $entity->$method($value);
//
//        $om->persist($entity, $prop);
//
//        $jobsDir = $om->getPaths('jobs');
//        $jobDir = $jobsDir.DIRECTORY_SEPARATOR.$entity->getId();
//        $propFile = $jobDir.DIRECTORY_SEPARATOR.$prop;
//
//        $this->assertStringEqualsFile($propFile, $value);
//    }

    /**
     * @depends      testConstructor
     *
     * @param JobTmpFSObjectManager $om
     */
    public function testRefreshJob(JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity();

        $om->persist($entity);

        $jobsDir = $om->getPaths('jobs');
        $jobDir = $jobsDir.DIRECTORY_SEPARATOR.$entity->getId();
        $statusFile = $jobDir.DIRECTORY_SEPARATOR.'status';

        file_put_contents($statusFile, '1');

        $om->refresh($entity);

        $this->assertEquals(1, $entity->getStatus());
    }

    /**
     * @dataProvider jobPropertiesProvider
     *
     * @param $prop
     * @param $value
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
        $this->om->findJob($sha1);
    }

    /**
     * @expectedException              \Bnza\JobRunnerBundle\Exception\JobRunnerEntityNotFoundException
     * @expectedExceptionMessageRegExp /".+" job not found/
     */
    public function testFindNotFoundId()
    {
        $this->om = new JobTmpFSObjectManager();

        $this->om->findJob('d54eff505dadd03c2dcdd71619562fdf449bd74e');
    }

    public function taskPropertiesProvider()
    {
        return [
            ['class', self::class],
            ['current_step_num', 11],
        ];
    }

    /**
     * @depends      testConstructor
     *
     * @param JobTmpFSObjectManager $om
     *
     * @return JobTmpFSObjectManager
     */
    public function testGetJobWorkDir(JobTmpFSObjectManager $om)
    {
        $path = $om->getJobWorkDir(self::JOB_ID);
        $this->assertEquals($om->getPaths('jobs').DIRECTORY_SEPARATOR.self::JOB_ID, $path);

        return $om;
    }

    /**
     * @depends      testGetJobWorkDir
     *
     * @param JobTmpFSObjectManager $om
     *
     * @return JobTmpFSObjectManager
     */
    public function testGetTasksWorkDir(JobTmpFSObjectManager $om)
    {
        $path = $om->getTasksWorkDir(self::JOB_ID);
        $this->assertEquals($om->getJobWorkDir(self::JOB_ID).DIRECTORY_SEPARATOR.JobTmpFSObjectManager::TASKS_DIR_NAME, $path);

        return $om;
    }

    /**
     * @depends      testGetTasksWorkDir
     *
     * @param JobTmpFSObjectManager $om
     *
     * @return JobTmpFSObjectManager
     */
    public function testGetTaskWorkDir(JobTmpFSObjectManager $om)
    {
        $path = $om->getTaskWorkDir(self::JOB_ID, self::TASK_NUM);
        $this->assertEquals($om->getTasksWorkDir(self::JOB_ID).DIRECTORY_SEPARATOR.self::TASK_NUM, $path);

        return $om;
    }

    /**
     * @depends      testGetJobWorkDir
     *
     * @param JobTmpFSObjectManager $om
     *
     * @return JobEntity
     */
    public function testPersistJob(JobTmpFSObjectManager $om): JobEntity
    {
        $entity = new JobEntity(self::JOB_ID);
        $om->persist($entity);

        $jobDir = $om->getJobWorkDir(self::JOB_ID);

        $this->assertFileExists($jobDir);

        return $entity;
    }

    /**
     * @dataProvider jobPropertiesProvider
     * @depends      testGetJobWorkDir
     *
     * @param $prop
     * @param $value
     * @param JobTmpFSObjectManager $om
     */
    public function testPersistJobProperty($prop, $value, JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);
        $inflector = new Inflector();
        $method = 'set'.$inflector->classify($prop);
        $entity->$method($value);

        $om->persist($entity, $prop);

        // $jobsDir = $om->getPaths('jobs');
        $jobDir = $om->getJobWorkDir(self::JOB_ID);
        $propFile = $jobDir.DIRECTORY_SEPARATOR.$prop;

        $this->assertStringEqualsFile($propFile, $value);
    }

    /**
     * @expectedException  \InvalidArgumentException
     * @expectedExceptionMessageRegExp /".+" is not a valid job property/
     * @depends testConstructor
     *
     * @param JobTmpFSObjectManager $om
     */
    public function testPersistJobWrongPropertyThrowsException(JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);
        $om->persist($entity, 'wrong_prop');
    }

    /**
     * @depends      testGetTaskWorkDir
     * @dataProvider taskPropertiesProvider
     *
     * @param $prop
     * @param $value
     * @param JobTmpFSObjectManager $om
     */
    public function testRefreshTaskProp($prop, $value, JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);
        $task = new TaskEntity($entity, self::TASK_NUM);

        $om->persist($task);

        $taskDir = $om->getTaskWorkDir(self::JOB_ID, self::TASK_NUM);
        $propFile = $taskDir.DIRECTORY_SEPARATOR.$prop;

        file_put_contents($propFile, $value);

        $om->refresh($task, $prop);

        $inflector = new Inflector();
        $method = 'get'.$inflector->classify($prop);

        $this->assertEquals($value, $task->$method());
    }

    /**
     * @depends testGetTaskWorkDir
     *
     * @param JobTmpFSObjectManager $om
     */
    public function testRefreshTask(JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);
        $task = new TaskEntity($entity, self::TASK_NUM);

        $om->persist($task);

        $taskDir = $om->getTaskWorkDir(self::JOB_ID, self::TASK_NUM);

        $statusFile = $taskDir.DIRECTORY_SEPARATOR.'current_step_num';

        $stepNum = 45;
        file_put_contents($statusFile, $stepNum);

        $om->refresh($task);

        $this->assertEquals($stepNum, $task->getCurrentStepNum());
    }

    /**
     * @depends testGetTaskWorkDir
     * @dataProvider taskPropertiesProvider
     *
     * @param $prop
     * @param $value
     */
    public function testPersistTaskProperty($prop, $value, JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);
        $task = new TaskEntity($entity, self::TASK_NUM);
        $inflector = new Inflector();
        $method = 'set'.$inflector->classify($prop);
        $task->$method($value);

        $om->persist($task);

        $taskDir = $om->getTaskWorkDir(self::JOB_ID, self::TASK_NUM);

        $propFile = $taskDir.DIRECTORY_SEPARATOR.$prop;

        $this->assertStringEqualsFile($propFile, $value);
    }

    /**
     * @expectedException  \InvalidArgumentException
     * @expectedExceptionMessageRegExp /".+" is not a valid task property/
     * @depends testConstructor
     *
     * @param JobTmpFSObjectManager $om
     */
    public function testPersistTaskWrongPropertyThrowsException(JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);
        $task = new TaskEntity($entity, self::TASK_NUM);
        $om->persist($task, 'wrong_prop');
    }

    /**
     * @depends      testGetTaskWorkDir
     *
     * @param JobTmpFSObjectManager $om
     *
     * @return TaskEntity
     */
    public function testPersistTask(JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);
        $task = new TaskEntity($entity, self::TASK_NUM);
        $om->persist($task);

        $taskDir = $om->getTaskWorkDir(self::JOB_ID, self::TASK_NUM);

        $this->assertFileExists($taskDir);

        return $task;
    }

    /**
     * @depends      testGetJobWorkDir
     *
     * @param JobTmpFSObjectManager $om
     */
    public function testPersistJobNoTask(JobTmpFSObjectManager $om)
    {
        $entity = new JobEntity(self::JOB_ID);

        $om->persist($entity);

        $jobDir = $om->getJobWorkDir(self::JOB_ID);
        $statusFile = $jobDir.DIRECTORY_SEPARATOR.'status';

        $this->assertStringEqualsFile($statusFile, '0');
    }

    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}
