<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Tests\Unit\Output;

use MakinaCorpus\Monitoring\Output\CounterGroup;
use PHPUnit\Framework\TestCase;

final class CounterGroupTest extends TestCase
{
    public function testCounterBasics(): void
    {
        $group = new CounterGroup('Foo');
        $group->add('Bar', 1);
        $group->add('Baz', 2);

        self::assertSame('Foo', $group->getTitle());
        $counters = $group->getCounters();
        self::assertSame('Bar', $counters[0]->getTitle());
        self::assertSame(1.0, $counters[0]->getValue());
        self::assertSame('Baz', $counters[1]->getTitle());
        self::assertSame(2.0, $counters[1]->getValue());
    }
}
