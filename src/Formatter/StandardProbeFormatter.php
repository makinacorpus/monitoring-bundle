<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Formatter;

use MakinaCorpus\Monitoring\ProbeStatus;

final class StandardProbeFormatter
{
    /**
     * Status to text.
     */
    public static function statusToText(int $status): string
    {
        switch ($status) {

            case 0:
                return "OK";

            case 1:
                return "WARNING";

            case 2:
                return "CRITICAL";

            default:
                return "UNKNOWN";
        }
    }

    /**
     * Format probe result.
     */
    public function format(ProbeStatus $status, ?string $name = null): string
    {
        $statusText = self::statusToText($status->getStatusCode());

        $messages = $status->getMessages();
        if ($messages) {
            $messageText = \implode(', ', $messages);
            if ($name) {
                return \sprintf("%s %s - %s", $name, $statusText, $messageText);
            }
            return \sprintf("%s - %s", $statusText, $messageText);
        } else if ($name) {
            return \sprintf("%s %s", $name, $statusText);
        }
        return \sprintf("%s", $statusText);
    }

    /**
     * When probe malfunctionned.
     */
    public function formatException(\Throwable $e, ?string $name = null): string
    {
        if ($name) {
            return \sprintf("%s - UNKNOWN | %s", $name, TextUtils::normalizeExceptionTrace($e));
        }
        return \sprintf("UNKNOWN | %s", TextUtils::normalizeExceptionTrace($e));
    }
}
