<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Formatter;

use MakinaCorpus\Monitoring\Output\Collection;

/**
 * Uses an output formatter to format a global report recursively.
 */
final class StandardReportFormatter
{
    public function format(OutputFormatter $formatter, Collection $collection, int $startLevel = 1): string
    {
        return "\n".$this->doFormat($formatter, $collection, $startLevel - 1);
    }

    private function doFormat(OutputFormatter $formatter, Collection $collection, int $level = 0): string
    {
        $innerDepth = $level;
        if ($title = $collection->getTitle()) {
            $innerDepth++;
        }

        $output = [];
        foreach ($collection as $item) {
            if ($item instanceof Collection) {
                $output[] = $this->doFormat($formatter, $item, $innerDepth);
            } else {
                $content = $formatter->item($item);
                if ($time = $item->getExecutionTime()) {
                    $content = $formatter->time($title, $time*1000, 'ms').$content;
                }
                if ($itemTitle = $item->getTitle()) {
                    $output[] = $formatter->section($itemTitle, $content, $innerDepth + 1);
                } else {
                    $output[] = $formatter->section(null, $content, $innerDepth);
                }
            }
        }

        if (!empty($output)) {
            return $formatter->section($title, \implode("\n", $output), $innerDepth);
        }
        return '';
    }
}
