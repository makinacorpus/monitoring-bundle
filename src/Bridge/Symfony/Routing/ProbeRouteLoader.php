<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony\Routing;

use MakinaCorpus\Monitoring\ProbeRegistry;
use MakinaCorpus\Monitoring\Bridge\Symfony\Controller\MonitoringController;
use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Generate routes for probe status.
 */
final class ProbeRouteLoader implements RouteLoaderInterface
{
    /** @var ProbeRegistry */
    private $probeRegistry;

    /**
     * Default constructor.
     */
    public function __construct(ProbeRegistry $probeRegistry)
    {
        $this->probeRegistry = $probeRegistry;
    }

    /**
     * Clean identifier
     */
    private static function path(string $format, ...$params): string
    {
        return \preg_replace('@[^a-zA-Z0-9-/\{\}]+@', '-', \sprintf($format, ...$params));
    }

    /**
     * Compute single probe route name
     */
    public static function getProbeRoute(string $name): string
    {
        return 'monitoring_status_'.self::path($name);
    }

    /**
     * {@inheritdoc}
     */
    public function load(): RouteCollection
    {
        $ret = new RouteCollection();

        foreach ($this->probeRegistry->getAllProbeNames() as $name) {
            $normalizedName = self::path($name);
            $ret->add(
                self::getProbeRoute($name),
                new Route(
                    '/monitoring/status/'.$normalizedName,
                    [
                        '_controller' => MonitoringController::class.'::status', 
                        'name' => $name,
                    ],
                    [],
                    /* array $options = */ [],
                    /* ?string $host = */ '',
                    /* $schemes = */ [],
                    ['GET']
                )
            );
        }

        return $ret;
    }
}
