<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Formatter;

final class TextUtils
{
    /**
     * Escape string for plain text
     */
    public static function escapePlain(string $string): string
    {
        return $string; // @todo?
    }

    /**
     * Escape string for HTMl
     */
    public static function escapeHtml(string $string): string
    {
        return \htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * New lines to HTML br
     */
    public static function nl2br(string $string): string
    {
        return \nl2br($string);
    }

    /**
     * Indent text with given string
     */
    public static function indentWithString(string $string, string $indent, bool $withFirstLine = true): string
    {
        return ($withFirstLine ? $indent : '').\str_replace(
            "\n",
            "\n".$indent,
            $string
        );
    }

    /**
     * Indent text with count space characters
     */
    public static function indent(string $string, int $count, bool $withFirstLine = true): string
    {
        return self::indentWithString($string, \str_repeat(" ", $count), $withFirstLine);
    }

    /**
     * Normalize exception trace
     */
    public static function normalizeExceptionTrace(\Throwable $exception): string
    {
        $output = '';
        do {
            if ($output) {
                $output .= "\n";
            }
            $output .= \sprintf("%s: %s\n", \get_class($exception), $exception->getMessage());
            $output .= $exception->getTraceAsString();
        } while ($exception = $exception->getPrevious());

        return $output;
    }
}
