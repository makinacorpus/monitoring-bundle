<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Formatter;

use MakinaCorpus\Monitoring\Output\Displayable;

interface OutputFormatter
{
    /**
     * Format new info collector section.
     *
     * @param string $title
     *   When section is a single item, it will probably be the item title
     * @param string $content
     *   If your formatted needs special escaping, consider this variable
     *   contents already being properly escaped (else you'll experience
     *   double escaping issues).
     * @parma int $level
     *   Level, higher is deeper, starts with 1
     */
    public function section(?string $title, string $content, int $level = 1): string;

    /**
     * Format execution time
     */
    public function time(string $title, float $value, ?string $unit = null): string;

    /**
     * Display a single item.
     */
    public function item(Displayable $item): string;
}
