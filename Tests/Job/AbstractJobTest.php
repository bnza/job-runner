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

use Bnza\JobRunnerBundle\Job\AbstractJob;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Bnza\JobRunnerBundle\Job\OM\JobObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

class AbstractJobTest extends \PHPUnit\Framework\TestCase
{
    use TmpFSTestFixturesTrait;

    /**
     * @var AbstractJob
     */
    private $jobMock;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    private $om;

    private $pb;

    public function setAbstractJobMock(EventDispatcher $dispatcher = null, JobObjectManagerInterface $om = null, ParameterBag $pb = null)
    {
        $this->dispatcher = $dispatcher ?: new EventDispatcher();

        $this->om = $om ?: new JobTmpFSObjectManager();

        $this->pb = $pb ?: new ParameterBag();

        $this->jobMock = $this->getMockForAbstractClass(
            AbstractJob::class,
            [
                $this->dispatcher,
                $this->om,
                $this->pb
            ]
        );
    }

    public function testConstructor()
    {
        $this->setAbstractJobMock();

        $id = $this->jobMock->getId();
        // $this->assertEquals(AbstractJob::class, $this->jobStub->getClass());
        $this->assertTrue(ctype_xdigit($id) && strlen($id) === 40);
    }



    public function tearDown()
    {
        $this->removeTestJobDirectory();
    }
}