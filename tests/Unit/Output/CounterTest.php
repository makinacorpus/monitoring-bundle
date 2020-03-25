<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Tests\Unit\Output;

use MakinaCorpus\Monitoring\Output\Counter;
use PHPUnit\Framework\TestCase;

final class CounterTest extends TestCase
{
    public function testCounterBasics(): void
    {
        $counter = new Counter('foo', 12.345678, 'milimeters', null);

        self::assertSame('foo', $counter->getTitle());
        self::assertSame(12.345678, $counter->getValue());
        self::assertSame('milimeters', $counter->getUnit());
    }

    public function testCounterWithRound(): void
    {
        $counter = new Counter('foo', 12.345678, 'milimeters', 3);

        self::assertSame('foo', $counter->getTitle());
        self::assertSame(12.346, $counter->getValue());
        self::assertSame('milimeters', $counter->getUnit());
    }
}
