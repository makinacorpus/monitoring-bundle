<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Tests\Unit;

use PHPUnit\Framework\TestCase;
use MakinaCorpus\Monitoring\ProbeStatus;

final class ProbeResultTest extends TestCase
{
    public function testNegativeStatusRaiseError(): void
    {
        self::expectException(\InvalidArgumentException::class);

        ProbeStatus::create(-1);
    }

    public function testInvalidStatusRaiseError(): void
    {
        self::expectException(\InvalidArgumentException::class);

        ProbeStatus::create(12);
    }

    public function testGetGetStatusCode(): void
    {
        self::assertSame(ProbeStatus::RESULT_OK, ProbeStatus::ok()->getStatusCode());
        self::assertSame(ProbeStatus::RESULT_WARNING, ProbeStatus::warning()->getStatusCode());
        self::assertSame(ProbeStatus::RESULT_CRITICAL, ProbeStatus::critical()->getStatusCode());
        self::assertSame(ProbeStatus::RESULT_UNKNOWN, ProbeStatus::unknown()->getStatusCode());
        self::assertSame(ProbeStatus::RESULT_WARNING, ProbeStatus::create(ProbeStatus::RESULT_WARNING)->getStatusCode());
    }

    public function testMessageAsString(): void
    {
        $result = ProbeStatus::ok("This is a message");

        self::assertSame(["This is a message"], $result->getMessages());
    }

    public function testGetMessagesWithIterable(): void
    {
        $generator = static function () {
            yield "Foo";
            yield "Bar";
            yield "Baz";
        };

        $result = ProbeStatus::ok($generator());

        self::assertSame(
            ["Foo", "Bar", "Baz"],
            $result->getMessages()
        );
    }
}
