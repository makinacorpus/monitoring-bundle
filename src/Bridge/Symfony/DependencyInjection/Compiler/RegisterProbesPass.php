<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection\Compiler;

use MakinaCorpus\Monitoring\DefaultProbeRegistry;
use MakinaCorpus\Monitoring\InfoCollector;
use MakinaCorpus\Monitoring\Probe;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class RegisterProbesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     * @psalm-suppress PossiblyNullArgument
     */
    public function process(ContainerBuilder $container)
    {
        $registryDefinition = $container->getDefinition('monitoring.registry');
        if (DefaultProbeRegistry::class !== $registryDefinition->getClass()) {
            return;
        }

        $instances = [];
        foreach ($container->findTaggedServiceIds('monitoring_plugin', true) as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            if (!$reflection = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if (!$reflection->implementsInterface(Probe::class) && !$reflection->implementsInterface(InfoCollector::class)) {
                throw new InvalidArgumentException(\sprintf(
                    'Service "%s" must implement interface "%s", "%s" or both.',
                    $id, Probe::class, InfoCollector::class
                ));
            }
            $instances[] = new Reference($id);
        }

        if ($instances) {
            $registryDefinition->setArgument(0, $instances);
        }
    }
}
