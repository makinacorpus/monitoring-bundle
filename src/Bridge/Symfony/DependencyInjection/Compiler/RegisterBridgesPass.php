<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection\Compiler;

use MakinaCorpus\Monitoring\Bridge\Goat\Query\PgSQLSchemaInfoCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

final class RegisterBridgesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     * @psalm-suppress PossiblyNullArgument
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('goat.runner.default')) {
            $container->setDefinition(
                PgSQLSchemaInfoCollector::class,
                (new Definition())
                    ->setClass(PgSQLSchemaInfoCollector::class)
                    ->setPublic(false)
                    ->setArguments([new Reference('goat.runner.default')])
                    ->addTag('monitoring_plugin')
            );
        }

        /*
        if ($container->hasDefinition(PgSQLTransportFactory::class)) {
            $container->setDefinition(
                MessageBrokerProbe::class,
                (new Definition())
                    ->setClass(MessageBrokerProbe::class)
                    ->setPublic(false)
                    ->setArgument(0, new Reference(Runner::class))
                    ->addTag('monitoring_plugin')
            );
        }
         */
    }
}
