<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Tests\Job;

use Bnza\JobRunnerBundle\Job\AbstractTask;
use Symfony\Component\Filesystem\Filesystem;
use Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\TaskEntity;

trait TmpFSFixturesTrait
{
    /**
     * @var JobTmpFSObjectManager
     */
    private $om;

    /**
     * @var JobEntity
     */
    private $jobEntity;

    /**
     * @var TaskEntity
     */
    private $taskEntity;

    public function setUpOm()
    {
        $this->om = new JobTmpFSObjectManager();
    }

    public function removeTestJobDirectory()
    {
        if ($this->om && $this->hasDependencies()) {
            //Remove env work dir (test)
            $dir = $this->om->getPaths('env');
            if (file_exists($dir)) {
                $fs = new Filesystem();
                $fs->remove($dir);
            }
        }
    }

    protected function getPersistedJobEntity(string $prop = '', $value = false): JobEntity
    {
        $this->om = new JobTmpFSObjectManager();

        $this->jobEntity = $entity = new JobEntity();

        if ($prop) {
            $method = 'set'.$prop;
            $entity->$method($value);
        }

        $this->om->persist($entity);

        return $entity;
    }

    protected function getPersistedTaskEntity($job = null, $num = 0, string $prop = '', $value = false): TaskEntity
    {
        $job = $job ?: $this->getPersistedJobEntity();

        $this->taskEntity = $entity = new TaskEntity($job, $num);

        $entity->setClass(AbstractTask::class);

        if ($prop) {
            $method = 'set'.$prop;
            $entity->$method($value);
        }

        $this->om->persist($entity);

        return $entity;
    }
}
