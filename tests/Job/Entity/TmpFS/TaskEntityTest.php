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
 * Date: 01/11/18
 * Time: 12.44.
 */

namespace Bnza\JobRunnerBundle\Tests\Job\Entity\TmpFS;

use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\TaskEntity;

class TaskEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testJobObjectConstructor()
    {
        $sha1 = '75228a0dd3e56aa2203d50c5867eac759ef6f322';
        $job = new JobEntity($sha1);
        $entity = new TaskEntity($job, 0);
        $this->assertEquals(0, $entity->getNum());
    }

    public function testStringJobIdConstructor()
    {
        $sha1 = '75228a0dd3e56aa2203d50c5867eac759ef6f322';
        $entity = new TaskEntity($sha1, 0);
        $this->assertEquals(0, $entity->getNum());
    }
}
