<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Tests\DependencyInjection;

use Bnza\JobRunnerBundle\DependencyInjection\BnzaJobRunnerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BnzaJobRunnerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Exception
     */
    public function testLoad()
    {
        $container = new ContainerBuilder();

        $extension = new BnzaJobRunnerExtension();

        $extension->load([], $container);

        $this->assertInstanceOf(BnzaJobRunnerExtension::class, $extension);

        //$this->assertEquals('test', $container->getParameter('app_env'));
    }
}
