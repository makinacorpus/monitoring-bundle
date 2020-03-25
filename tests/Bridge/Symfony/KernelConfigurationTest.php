<?php

declare(strict_types=1);

namespace GeneratedHydrator\Bridge\Symfony\Tests\Functionnal;

use MakinaCorpus\Monitoring\DefaultProbeRegistry;
use MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection\MonitoringExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class KernelConfigurationTest extends TestCase
{
    private function getContainer()
    {
        // Code inspired by the SncRedisBundle, all credits to its authors.
        return new ContainerBuilder(new ParameterBag([
            'kernel.debug'=> false,
            'kernel.bundles' => [],
            'kernel.cache_dir' => \sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir' => \dirname(__DIR__),
        ]));
    }

    private function getMinimalConfig(): array
    {
        return [
            'info_collector_tags' => [
                // 'foo' => "The 'Foo' tag.",
            ],
        ];
    }

    /**
     * Test default config for resulting tagged services
     */
    public function testTaggedServicesConfigLoad()
    {
        $extension = new MonitoringExtension();
        $config = $this->getMinimalConfig();
        $extension->load([$config], $container = $this->getContainer());

        foreach ([
            'monitoring.registry',
        ] as $serviceId) {
            self::assertTrue($container->hasDefinition($serviceId));
        }

        self::assertSame(DefaultProbeRegistry::class, $container->getDefinition('monitoring.registry')->getClass());
    }
}
