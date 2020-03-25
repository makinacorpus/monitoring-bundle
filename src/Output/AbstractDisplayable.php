<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Output;

abstract class AbstractDisplayable implements Displayable
{
    /** @var ?string */
    private $title = null;

    /** @var ?float */
    private $executionTime = null;

    public function __construct(?string $title = null)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title
     */
    public function setTitle(?string $title = null): void
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    /**
     * Set execution time
     *
     * @param float $executionTime
     *   Time in seconds.
     */
    public function setExecutionTime(float $executionTime): void
    {
        $this->executionTime = $executionTime;
    }
}
