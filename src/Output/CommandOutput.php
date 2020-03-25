<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Output;

final class CommandOutput extends AbstractDisplayable
{
    /** @var string */
    private $command;

    /** @var string */
    private $output;

    public function __construct(string $command, string $output, ?string $title = null)
    {
        parent::__construct($title);

        $this->command = $command;
        $this->output = $output;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}
