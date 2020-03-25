<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Formatter;

use MakinaCorpus\Monitoring\Output\CommandOutput;
use MakinaCorpus\Monitoring\Output\Counter;
use MakinaCorpus\Monitoring\Output\CounterGroup;
use MakinaCorpus\Monitoring\Output\Displayable;
use MakinaCorpus\Monitoring\Output\Table;

class HtmlOutputFormatter implements OutputFormatter
{
    /**
     * {@inheritdoc}
     */
    public function section(?string $title, string $content, int $level = 1): string
    {
        if (!$title) {
            return '<div class="monitoring-section">'."\n".$content."\n</div>\n";
        }

        $escapedTitle = TextUtils::escapeHtml($title);
        if ($level <= 6) {
            $escapedTitle = \sprintf("<h%d>%s</h%d>", $level, $escapedTitle, $level);
        } else {
            $escapedTitle = \sprintf("<p><strong>%s</strong></p>", $level, $escapedTitle, $level);
        }

        return '<div class="monitoring-section">'."\n".$escapedTitle."\n".$content."\n</div>\n";
    }

    /**
     * {@inheritdoc}
     */
    public function time(string $title, float $value, ?string $unit = null): string
    {
        return "<p><small>Execution time: ".\round($value, 2)." ".$unit."</small></p>\n";
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
        $headerString = '';
        if ($headers = $table->getHeaders()) {
            $headerString = '<thead><tr>'."\n";
            foreach ($headers as $value) {
                $headerString .= '<th>'.TextUtils::escapeHtml($value).'</th>'."\n";
            }
            $headerString .= '</tr></thead>'."\n";
        }

        $ret = '<table class="table table-striped table-hover table-sm table-bordered">'."\n";
        $ret .= $headerString."<tbody>\n";

        foreach ($table->getRows() as $row) {
            $ret .= '<tr>'."\n";
            foreach ($row as $value) {
                if ($value instanceof Displayable) {
                    $value = $this->item($value);
                }
                $ret .= '<td>'.TextUtils::nl2br((string)$value).'</td>'."\n"; // @todo escaping?
            }
            $ret .= '</tr>'."\n";
        }

        return $ret.'</tbody></table>'."\n";
    }

    private function counterGroup(CounterGroup $counter): string
    {
        $output = '<dl>'."\n";
        foreach ($counter->getCounters() as $counter) {
            \assert($counter instanceof Counter);
            $value = TextUtils::escapeHtml((string)$counter->getValue());
            if ($unit = $counter->getUnit()) {
                $value .= '&nbsp;'.TextUtils::escapeHtml($unit);
            }
            $output .= '<dt>'.TextUtils::escapeHtml($counter->getTitle()).'</dt>'."\n";
            $output .= '<dd>'.TextUtils::nl2br($value).'</dd>'."\n";
        }
        return $output.'</dl>'."\n";
    }

    private function counter(Counter $counter): string
    {
        $output = '<dl>'."\n";
        $value = TextUtils::escapeHtml((string)$counter->getValue());
        if ($unit = $counter->getUnit()) {
            $value .= '&nbsp;'.TextUtils::escapeHtml($unit);
        }
        $output .= '<dt>'.TextUtils::escapeHtml($counter->getTitle()).'</dt>'."\n";
        $output .= '<dd>'.TextUtils::nl2br($value).'</dd>'."\n";
        return $output.'</dl>'."\n";
    }

    private function commandOutput(CommandOutput $commandOutput): string
    {
        return "<pre>\n $ ".$commandOutput->getCommand()."\n".$commandOutput->getOutput()."\n</pre>\n";
    }
}
