<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Goat\Domain;

use Goat\Runner\Runner;
use MakinaCorpus\Monitoring\Probe;
use MakinaCorpus\Monitoring\ProbeStatus;

/**
 * Check for "message_broker" unconsumed messages.
 */
final class MessageBrokerProbe implements Probe
{
    /** @var ?int */
    private $warningThreshold;

    /** @var ?int */
    private $criticalThreshold;

    /** @var Runner */
    private $runner;

    public function __construct(Runner $runner, ?int $warningThreshold = null, ?int $criticalThreshold = null)
    {
        $this->runner = $runner;
        $this->warningThreshold = $warningThreshold ?? 500;
        $this->criticalThreshold = $criticalThreshold ?? 1000;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'message_broker';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return "Message broker";
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): ProbeStatus
    {
        $queueSize = $this->runner->execute('SELECT COUNT(*) FROM "message_broker" WHERE "consumed_at" is null')->fetchField();

        // For those who know Nagios or the like, just set a very short status
        // message intended for display purpose in larger reports.
        $message = \sprintf("message broker size: %d items", $queueSize);

        if ($queueSize >= $this->criticalThreshold) {
            return ProbeStatus::critical($message);
        }
        if ($queueSize >= $this->warningThreshold) {
            return ProbeStatus::warning($message);
        }
        return ProbeStatus::ok($message);
    }
}
