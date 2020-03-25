<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Output;

final class Counter extends AbstractDisplayable
{
    /** @var float */
    private $count;

    /** @var ?int */
    private $round = null;

    /** @var ?string */
    private $unit = null;

    public function __construct(string $title, float $count, ?string $unit = null, ?int $round = null)
    {
        parent::__construct($title);

        $this->count = $count;
        $this->unit = $unit;
        $this->round = $round;
    }

    public function getValue(): float
    {
        if ($this->round) {
            return \round($this->count, $this->round);
        }
        return $this->count;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }
}
