<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Output;

/**
 * Common type for all displayable items.
 */
interface Displayable
{
    /**
     * Get title, if any
     */
    public function getTitle(): ?string;

    /**
     * Time it took to execute, in seconds.
     */
    public function getExecutionTime(): ?float;
}
