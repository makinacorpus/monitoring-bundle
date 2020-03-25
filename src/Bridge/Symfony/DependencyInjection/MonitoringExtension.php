<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\Monitoring\DefaultProbeRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class MonitoringExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @psalm-suppress PossiblyNullArgument
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yaml');

        if (\class_exists(Command::class)) {
            $loader->load('console.yaml');
        }

        $this->registerInfoCollectorTagNames($container, $config);
    }

    /**
     * Create PSR-4 factory object.
     */
    private function registerInfoCollectorTagNames(ContainerBuilder $container, array $config): void
    {
        if (empty($config['info_collector_tags'])) {
            return;
        }

        $registryDefinition = $container->getDefinition('monitoring.registry');
        if (DefaultProbeRegistry::class !== $registryDefinition->getClass()) {
            return;
        }

        $registryDefinition->setArgument(1, $config['info_collector_tags']);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new MonitoringConfiguration();
    }
}
