<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony;

use MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection\MonitoringExtension;
use MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection\Compiler\RegisterBridgesPass;
use MakinaCorpus\Monitoring\Bridge\Symfony\DependencyInjection\Compiler\RegisterProbesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MonitoringBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterBridgesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        $container->addCompilerPass(new RegisterProbesPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new MonitoringExtension();
    }
}
