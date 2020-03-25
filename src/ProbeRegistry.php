<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring;

interface ProbeRegistry
{
    /**
     * Get info collectors with tags.
     *
     * Use case: fetching probes for an admin screen.
     *
     * @return list<InfoCollector>
     */
    public function getInfoCollectorsWithTag(string $tag): iterable;

    /**
     * Get all known info collector tags.
     *
     * Use case: display menu in 
     *
     * @return array<string, string>
     *   Keys are tags, values are human readable labels
     */
    public function getInfoCollectorTags(): array;

    /**
     * Get a tag human readable label
     */
    public function getTagLabel(string $tag): string;

    /**
     * Get a single probe
     *
     * @throws \InvalidArgumentException
     */
    public function getProbe(string $name): Probe;

   /**
     * Iterate over all probes names.
     *
     * Use case: build routes for probes.
     *
     * @return list<string>
     *   Probe names.
     */
    public function getAllProbeNames(): iterable;

    /**
     * Iterate over all probes.
     *
     * Use case: cron task to do all monitoring checks.
     *
     * @return iterable<string, Probe>
     *   Keys are probe names, values are instances
     */
    public function getAllProbes(): iterable;
}
