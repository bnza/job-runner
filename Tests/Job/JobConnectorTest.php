<?php
/**
 *
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 30/10/18
 * Time: 9.36
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\JobStatus;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\JobConnector;

class JobConnectorTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSTestFixturesTrait;

    public function testConstructor()
    {
        $this->om = new JobTmpFSObjectManager();

        $entity = new JobEntity();

        $this->om->persist($entity);

        $connector = new JobConnector($this->om, $entity->getId());

        $this->assertEquals($entity->getId(), $connector->getId());
    }

    public function testCancel()
    {
        $this->om = new JobTmpFSObjectManager();

        $entity = new JobEntity();

        $this->om->persist($entity);

        $connector = new JobConnector($this->om, $entity->getId());

        $connector->cancel();

        $this->assertTrue(JobStatus::isCancelled($connector->getStatus()));
    }

    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}