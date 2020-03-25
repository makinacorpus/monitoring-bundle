<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring;

interface Probe extends Identifiable
{
    /**
     * Status OK
     */
    const RESULT_OK = 0;

    /**
     * Status warning: send alarm.
     */
    const RESULT_WARNING = 1;

    /**
     * Status error: send alarm, attempt repair.
     */
    const RESULT_ERROR = 2;

    /**
     * Get status
     */
    public function getStatus(): ProbeStatus;
}
