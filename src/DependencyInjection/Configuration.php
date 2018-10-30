<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bnza_job_runner');

        $rootNode
            ->children()
            //->scalarNode('env')->defaultValue($this->env)->end()
            //->scalarNode('env')->end()
            //->scalarNode('base_path')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
