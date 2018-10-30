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
 * Time: 9.36.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\JobStatus;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\JobConnector;

class JobConnectorTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSFixturesTrait;

    public function testConstructor()
    {
        $entity = $this->getPersistedJobEntity();
        $connector = new JobConnector($this->om, $entity->getId());
        $this->assertEquals($entity->getId(), $connector->getId());
    }

    public function connectorPropertiesProvider()
    {
        return [
            ['Status', 0b0100],
            ['Class', self::class],
            ['CurrentTaskNum', 100],
            ['Name', 'Test name'],
            ['TasksNum', 2],
            ['Error', 'Bad error'],
        ];
    }

    /**
     * @dataProvider connectorPropertiesProvider
     *
     * @param $prop
     * @param $value
     */
    public function testGetProperties($prop, $value)
    {
        $entity = $this->getPersistedJobEntity($prop, $value);

        $connector = new JobConnector($this->om, $entity->getId());

        $method = 'get'.$prop;

        $this->assertEquals($value, $connector->$method());
    }

    public function testCancel()
    {
        $this->om = new JobTmpFSObjectManager();

        $entity = new JobEntity();

        $entity->setStatus(JobStatus::setStatus($entity->getStatus(), JobStatus::RUNNING, true));

        $this->om->persist($entity);

        $connector = new JobConnector($this->om, $entity->getId());

        $connector->cancel();

        $this->assertTrue(JobStatus::isCancelled($connector->getStatus()));
    }

    /**
     * @expectedException           \RuntimeException
     * @expectedExceptionMessage    Only running job can be cancelled
     */
    public function testCancelException()
    {
        $this->om = new JobTmpFSObjectManager();

        $entity = new JobEntity();

        $this->om->persist($entity);

        $connector = new JobConnector($this->om, $entity->getId());

        $connector->cancel();

        $connector->cancel();
    }

    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}
