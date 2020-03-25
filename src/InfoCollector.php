<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring;

use MakinaCorpus\Monitoring\Output\CollectionBuilder;

interface InfoCollector extends Identifiable
{
    /**
     * Get tags
     *
     * @return list<string>
     *   Tag names.
     */
    public function getTags(): iterable;

    /**
     * Get information
     */
    public function info(CollectionBuilder $builder): void;
}
