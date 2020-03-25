<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Event;

use MakinaCorpus\Monitoring\ProbeStatus;

final class ProbeResultEvent
{
    /** @var string */
    private $name;

    /** @var ProbeStatus */
    private $status;

    public function __construct(string $name, ProbeStatus $status)
    {
        $this->name = $name;
        $this->status = $status;
    }

    public function isWarning(): bool
    {
        return ProbeStatus::RESULT_WARNING === $this->status->getStatusCode();
    }

    public function isCritical(): bool
    {
        return ProbeStatus::RESULT_CRITICAL === $this->status->getStatusCode();
    }

    public function isMalfunctioning(): bool
    {
        return ProbeStatus::RESULT_UNKNOWN === $this->status->getStatusCode();
    }

    public function getProbeName(): string
    {
        return $this->name;
    }

    public function getProbeStatus(): ProbeStatus
    {
        return $this->status;
    }
}
