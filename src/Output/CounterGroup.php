<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Output;

final class CounterGroup extends AbstractDisplayable
{
    /** @var list<Counter> */
    private $counters;

    public function add(string $title, float $count, ?string $unit = null, ?int $round = null): self
    {
        $this->counters[] = new Counter($title, $count, $unit, $round);

        return $this;
    }

    /**
     * @return list<Counter>
     */
    public function getCounters(): iterable
    {
        return $this->counters;
    }
}
