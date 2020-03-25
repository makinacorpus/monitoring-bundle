<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring;

interface Identifiable
{
    /**
     * Get identifier.
     */
    public function getName(): string;

    /**
     * Get human readable title for admin screens.
     */
    public function getTitle(): string;
}
