<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring;

final class DefaultProbeRegistry implements ProbeRegistry
{
    /** @var bool */
    private $initialized = false;

    /** @var iterable */
    private $instances = [];

    /** @var array<string, list<InfoCollector>> */
    private $infoCollectors = [];

    /** @var array<string, string> */
    private $infoCollectorTags = [];

    /** @var array<string, string> */
    private $tagNames = [];

    /** @var array<string, Probe> */
    private $probes = [];

    /**
     * Default constructor
     */
    public function __construct(iterable $instances, array $tagNames = [])
    {
        $this->instances = $instances;
        $this->tagNames = $tagNames;
    }

    /**
     * Internal initialization.
     */
    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        foreach ($this->instances as $instance) {
            $found = false;

            if ($instance instanceof Probe) {
                $found = true;
                $this->probes[$instance->getName()] = $instance;
            }

            if ($instance instanceof InfoCollector) {
                $found = true;
                foreach ($instance->getTags() as $tag) {
                    $this->infoCollectors[$tag][] = $instance;
                    $this->infoCollectorTags[$tag] = $this->tagNames[$tag] ?? $tag;
                }
            }

            if (!$found) {
                throw new \InvalidArgumentException(\sprintf(
                    "Instance must be an instance of '%s', or '%s', or both",
                    Probe::class, InfoCollector::class
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoCollectorsWithTag(string $tag): iterable
    {
        $this->initialize();

        return $this->infoCollectors[$tag] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoCollectorTags(): array
    {
        $this->initialize();

        return $this->infoCollectorTags;
    }

    /**
     * {@inheritdoc}
     */
    public function getTagLabel(string $tag): string
    {
        $this->initialize();

        return $this->infoCollectorTags[$tag] ?? $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function getProbe(string $name): Probe
    {
        $this->initialize();

        if (!$probe = ($this->probes[$name] ?? null)) {
            throw new \InvalidArgumentException(\sprintf("'%s': probe does not exist", $name));
        }

        return $probe;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProbeNames(): iterable
    {
        $this->initialize();

        return \array_keys($this->probes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProbes(): iterable
    {
        $this->initialize();

        return $this->probes;
    }
}
