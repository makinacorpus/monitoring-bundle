<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Tests\Unit;

use MakinaCorpus\Monitoring\DefaultProbeRegistry;
use MakinaCorpus\Monitoring\InfoCollector;
use MakinaCorpus\Monitoring\Probe;
use MakinaCorpus\Monitoring\ProbeStatus;
use MakinaCorpus\Monitoring\Output\CollectionBuilder;
use PHPUnit\Framework\TestCase;

final class DefaultProbeRegistryTest extends TestCase
{
    private static function createDefaultProbeRegistry(array $tagNames = [])
    {
        return new DefaultProbeRegistry([
            new class() implements InfoCollector
            {
                public function getName(): string
                {
                    return 'Foo';
                }

                public function getTags(): iterable
                {
                    return ['foo'];
                }

                public function getTitle(): string { throw new \Exception(); }

                public function info(CollectionBuilder $builder): void { throw new \Exception(); }
            },

            new class() implements Probe
            {
                public function getName(): string
                {
                    return 'mooh';
                }

                public function getTitle(): string { throw new \Exception(); }

                public function getStatus(): ProbeStatus { return ProbeStatus::ok(); }
            },

            new class() implements InfoCollector
            {
                public function getName(): string
                {
                    return 'Bar';
                }

                public function getTags(): iterable
                {
                    return ['foo', 'bar'];
                }

                public function getTitle(): string { throw new \Exception(); }

                public function info(CollectionBuilder $builder): void { throw new \Exception(); }
            },

            new class() implements Probe
            {
                public function getName(): string
                {
                    return 'baah';
                }

                public function getTitle(): string { throw new \Exception(); }

                public function getStatus(): ProbeStatus { return ProbeStatus::ok(); }
            },
        ], $tagNames);
    }

    public function testInitializeRaiseErrorOnWrongInput(): void
    {
        $registry = new DefaultProbeRegistry([new \DateTime()]);

        self::expectException(\InvalidArgumentException::class);
        $registry->getAllProbes();
    }

    public function testInitializeSetTagNameAsLabel(): void
    {
        $registry = self::createDefaultProbeRegistry();

        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $registry->getInfoCollectorTags());
        self::assertSame('foo', $registry->getTagLabel('foo'));
    }

    public function testInitializeDropUnusedTags(): void
    {
        $registry = self::createDefaultProbeRegistry([
            'foo' => 'My Foo Tag',
            'moo' => 'Moooooh!',
        ]);

        self::assertSame(['foo' => 'My Foo Tag', 'bar' => 'bar'], $registry->getInfoCollectorTags());
        self::assertSame('My Foo Tag', $registry->getTagLabel('foo'));
    }

    public function testGetInfoCollectorsWithTag(): void
    {
        $registry = self::createDefaultProbeRegistry();

        self::assertSame(
            ['Bar'],
            \array_map(
                static function (InfoCollector $value) {
                    return $value->getName();
                },
                $registry->getInfoCollectorsWithTag('bar')
            )
        );

        self::assertSame(
            ['Foo', 'Bar'],
            \array_map(
                static function (InfoCollector $value) {
                    return $value->getName();
                },
                $registry->getInfoCollectorsWithTag('foo')
            )
        );

        self::assertSame([], $registry->getInfoCollectorsWithTag('moo'));
    }

    public function testGetProbe(): void
    {
        $registry = $this->createDefaultProbeRegistry();

        self::assertSame('mooh', $registry->getProbe('mooh')->getName());
    }

    public function testGetProbeRaiseErrorIfNotExists(): void
    {
        $registry = $this->createDefaultProbeRegistry();

        self::expectException(\InvalidArgumentException::class);

        $registry->getProbe('this_probe_does_not_exist');
    }

    public function testGetAllProbesNames(): void
    {
        $registry = self::createDefaultProbeRegistry();

        self::assertSame(['mooh', 'baah'], $registry->getAllProbeNames());
    }

    public function testGetAllProbes(): void
    {
        $registry = self::createDefaultProbeRegistry();

        self::assertSame(
            ['mooh' => 'mooh', 'baah' => 'baah'],
            \array_map(
                static function (Probe $value) {
                    return $value->getName();
                },
                $registry->getAllProbes()
            )
        );
    }
}
