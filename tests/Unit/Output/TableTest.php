<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Tests\Unit\Output;

use MakinaCorpus\Monitoring\Output\Table;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    private static function assertSameTable(array $expected, Table $table): void
    {
        $actual = [];

        if ($headers = $table->getHeaders()) {
            $actual[] = \is_array($headers) ? $headers : \iterator_to_array($headers);
        }

        foreach ($table->getRows() as $row) {
            $actual[] = \is_array($row) ? $row : \iterator_to_array($row);
        }

        self::assertSame($expected, $actual);
    }

    public function testGetSetTitle(): void
    {
        $table = new Table('My Title');

        self::assertSame('My Title', $table->getTitle());
    }

    public function testSetHeaderAcceptsArray(): void
    {
        $table = new Table();
        $table->setHeaders(['foo', 'bar']);

        self::assertSameTable([
            ['foo', 'bar']
        ], $table);
    }

    public function testAddRowWithInvalidValueRaiseError(): void
    {
        $table = new Table();

        self::expectException(\InvalidArgumentException::class);
        $table->addRow(["d", new \DateTime(), "f"]);
    }

    public function testAddRowAcceptsNumbers(): void
    {
        $table = new Table();
        $table->addRow([1, null, '', 3.7]);

        self::assertSameTable([
            ["1", null, "", "3.7"],
        ], $table);
    }

    public function testAddRowAcceptsToString(): void
    {
        $table = new Table();
        $table->addRow([new class() {
            public function __toString(): string
            {
                return "Hello";
            }
        }]);

        self::assertSameTable([
            ["Hello"],
        ], $table);
    }

    public function testAddRowAcceptsArray(): void
    {
        $table = new Table();
        $table->setHeaders(["first", "second", "third"]);
        $table->addRow(["a", "b", "c"]);
        $table->addRow(["d", "e", "f"]);

        self::assertSameTable([
            ["first", "second", "third"],
            ["a", "b", "c"],
            ["d", "e", "f"],
        ], $table);
    }
}
