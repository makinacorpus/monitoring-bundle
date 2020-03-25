<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Output;

/**
 * Collection builder
 */
final class CollectionBuilder
{
    /** @var array<int, Displayable> */
    private $items = [];

    /** @var ?string */
    private $title = null;

    public function __construct(?string $title = null)
    {
        $this->title = $title;
    }

    /**
     * Add item in collection
     */
    public function add(Displayable $item): void
    {
        $this->items[] = $item;
    }

    public function addTable(?string $title = null): Table
    {
        return $this->items[] = new Table($title);
    }

    public function addCommandOutput(string $command, string $output, ?string $title = null): CommandOutput
    {
        return $this->items[] = new CommandOutput($command, $output, $title);
    }

    public function addCounterGroup(string $title): CounterGroup
    {
        return $this->items[] = new CounterGroup($title);
    }

    public function addCounter(string $title, float $count, ?string $unit = null, ?int $round = null): Counter
    {
        return $this->items[] = new Counter($title, $count, $unit, $round);
    }

    /**
     * Get immutable collection object from builder
     */
    public function create(): Collection
    {
        return new class ($this->title, $this->items) implements \IteratorAggregate, Collection
        {
            /** @var ?string */
            private $title;

            /** @var array<int, Displayable> */
            private $items;

            public function __construct(?string $title, array $items)
            {
                $this->title = $title;
                $this->items = $items;
            }

            public function getIterator()
            {
                foreach ($this->items as $item) {
                    yield $item;
                }
            }

            public function getTitle(): ?string
            {
                return $this->title;
            }

            public function getExecutionTime(): ?float
            {
                return null;
            }
        };
    }
}
