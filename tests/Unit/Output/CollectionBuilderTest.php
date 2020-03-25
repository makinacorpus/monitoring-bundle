<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Tests\Unit\Output;

use MakinaCorpus\Monitoring\Output\AbstractDisplayable;
use MakinaCorpus\Monitoring\Output\Collection;
use MakinaCorpus\Monitoring\Output\CollectionBuilder;
use MakinaCorpus\Monitoring\Output\Counter;
use MakinaCorpus\Monitoring\Output\CounterGroup;
use MakinaCorpus\Monitoring\Output\Table;
use PHPUnit\Framework\TestCase;

final class CollectionBuilderTest extends TestCase
{
    public function testPrettyMuchEverything(): void
    {
        $builder = new CollectionBuilder();
        $builder->add(new Counter('foo', 2));
        $builder->add(new class() extends AbstractDisplayable {});
        $builder->add(new Table());
        $builder->addCounter('bar', 3);
        $builder->addTable();
        $builder->addCounterGroup('baz');

        $collection = $builder->create();
        self::assertInstanceOf(Collection::class, $collection);

        $array = \iterator_to_array($collection);
        self::assertInstanceOf(Counter::class, $array[0]);
        self::assertInstanceOf(Table::class, $array[2]);
        self::assertInstanceOf(Counter::class, $array[3]);
        self::assertInstanceOf(Table::class, $array[4]);
        self::assertInstanceOf(CounterGroup::class, $array[5]);
    }
}
