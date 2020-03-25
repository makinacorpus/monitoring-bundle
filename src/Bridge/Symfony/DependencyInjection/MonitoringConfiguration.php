<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class MonitoringConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('monitoring');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->variableNode('info_collector_tags')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
