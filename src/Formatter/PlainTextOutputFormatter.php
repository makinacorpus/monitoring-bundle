<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Formatter;

use MakinaCorpus\Monitoring\Output\CommandOutput;
use MakinaCorpus\Monitoring\Output\Counter;
use MakinaCorpus\Monitoring\Output\CounterGroup;
use MakinaCorpus\Monitoring\Output\Displayable;
use MakinaCorpus\Monitoring\Output\Table;
use Symfony\Component\Console\Helper\Table as ConsoleTable;
use Symfony\Component\Console\Output\BufferedOutput;

class PlainTextOutputFormatter implements OutputFormatter
{
    /**
     * {@inheritdoc}
     */
    public function section(?string $title, string $content, int $level = 1): string
    {
        if (!$title) {
            return $content;
        }

        $escapedTitle = TextUtils::escapePlain($title);
        $underline = ''; $underChar = null;

        switch ($level) {
            case 1:
                $underChar = '=';
                break;
            case 2:
                $underChar = '-';
                break;
            case 3:
                $underChar = '+';
                break;
        }

        if ($underChar) {
            $underline = "\n".\str_repeat($underChar, \mb_strlen($escapedTitle));
        }

        return $escapedTitle.$underline."\n\n".$content;
    }

    /**
     * {@inheritdoc}
     */
    public function time(string $title, float $value, ?string $unit = null): string
    {
        return "Execution time: ".\round($value, 2)." ".$unit."\n\n";
    }

    /**
     * {@inheritdoc}
     */
    public function item(Displayable $item): string
    {
        if ($item instanceof Counter) {
            return $this->counter($item);
        }
        if ($item instanceof CounterGroup) {
            return $this->counterGroup($item);
        }
        if ($item instanceof Table) {
            return $this->table($item);
        }
        if ($item instanceof CommandOutput) {
            return $this->commandOutput($item);
        }
        if (\method_exists($item, '__toString')) {
            return $item->__toString();
        }
        return \sprintf("ERROR: Cannot format item '%s'", \get_class($item));
    }

    private function table(Table $table): string
    {
        $output = new BufferedOutput();
        $outputTable = new ConsoleTable($output);

        if ($headers = $table->getHeaders()) {
            $outputTable->setHeaders($headers);
        }
        foreach ($table->getRows() as $row) {
            $outputTable->addRow(
                \array_map(
                    function ($value) {
                        if ($value instanceof Displayable) {
                            return $this->item($value);
                        }
                        return (string)$value;
                    },
                    $row
                )
            );
        }
        $outputTable->render();

        return $output->fetch();
    }

    private function counterRaw(Counter $counter): string
    {
        if ($unit = $counter->getUnit()) {
            return \sprintf("%s: %s %s", $counter->getTitle(), $counter->getValue(), $unit);
        }
        return \sprintf("%s: %s", $counter->getTitle(), $counter->getValue());
    }

    private function counter(Counter $counter): string
    {
        return $this->counterRaw($counter)."\n";
    }

    private function counterGroup(CounterGroup $counterGroup): string
    {
        $ret = [];
        foreach ($counterGroup->getCounters() as $counter) {
            $ret[] = "  - ".$this->counterRaw($counter);
        }
        if (!$ret) {
            $ret[] = "  - Nothing to display!";
        }

        return \implode("\n", $ret)."\n";
    }

    private function commandOutput(CommandOutput $commandOutput): string
    {
        return " $ ".$commandOutput->getCommand()."\n".$commandOutput->getOutput()."\n";
    }
}
