<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring;

use MakinaCorpus\Monitoring\Formatter\TextUtils;

final class ProbeStatus
{
    /** OK */
    const RESULT_OK = 0;

    /** Warning */
    const RESULT_WARNING = 1;

    /** Critical */
    const RESULT_CRITICAL = 2;

    /** Unknown */
    const RESULT_UNKNOWN = 3;

    /** @var int */
    private $status;

    /** @var list<string> */
    private $messages = [];

    /**
     * Default constructor
     */
    private function __construct(int $status, $messages = null)
    {
        if (0 > $status || 3 < $status) {
            throw new \InvalidArgumentException("Status must be 0 (OK), 1 (WARNING), 2 (CRITICAL) or 3 (UNKNOWN)");
        }
        if (null !== $messages) {
            if (\is_string($messages)) {
                $this->messages = [$messages];
            } else if (\is_array($messages)) {
                $this->messages = $messages;
            } else if (\is_iterable($messages)) {
                $this->messages = \iterator_to_array($messages);
            }
        }
        $this->status = $status;
    }

    /**
     * Result is OK.
     *
     * @param null|string|list<string> $messages
     */
    public static function ok($messages = null): self
    {
        return new self(self::RESULT_OK, $messages);
    }

    /**
     * Result is warning.
     *
     * @param null|string|list<string> $messages
     */
    public static function warning($messages = null): self
    {
        return new self(self::RESULT_WARNING, $messages);
    }

    /**
     * Result is critical.
     *
     * @param null|string|list<string> $messages
     */
    public static function critical($messages = null): self
    {
        return new self(self::RESULT_CRITICAL, $messages);
    }

    /**
     * Result is unknown.
     *
     * @param null|string|list<string> $messages
     */
    public static function unknown($messages = null): self
    {
        return new self(self::RESULT_UNKNOWN, $messages);
    }

    /**
     * Result is unknown.
     *
     * @param null|string|list<string> $messages
     */
    public static function unknownFromException(\Throwable $e): self
    {
        return new self(self::RESULT_UNKNOWN, [TextUtils::normalizeExceptionTrace($e)]);
    }

    /**
     * Result is critical.
     *
     * @param null|string|list<string> $messages
     */
    public static function create(int $status, $messages = null): self
    {
        return new self($status, $messages);
    }

    /**
     * Get status.
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Get status messages for report (on screen, in log, in mail).
     *
     * @return list<string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
