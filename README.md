# Simple monitoring tooling for Symfony

This API provides simple and easy to use interfaces for building supervision
probes that you can run to check sanity of your application.

It provides a Symfony >= 4.4 compatible bundle for registering your custom
probes into a Symfony application.

Features:

 - Build probes that restitute an output similar to commonly used supervision
   tools, such as Nagios or Centreon.
 - Build more advanced information collectors for building advanced status
   reports.
 - Provide an easy to use probe and info collectors registry.
 - Provide a few console commands to run probes or build status reports.
 - When used via the Symfony bundle, provide easy probes registration and
   HTTP endpoints for querying probes: easy to use with supervision tools.

# Installation

Install it using composer:

```bash
composer req makinacorpus/monitoring-bundle
```

# Symfony bundle

## Installation

Then register the bundle in your `app/bundles.php`:

```php
<?php

return [
    // Other bundles...
    MakinaCorpus\Monitoring\Bridge\Symfony\MonitoringBundle::class => ['all' => true],
];
```

## Configuration

Optionnaly copy the `src/Bridge/Symfony/Resources/config/packages/monitoring.yaml`
file into your app `config/packages/` folder.

## Register HTTP status endpoint (recommended)

Status endpoint will always return plain text responses, Nagios parser compatible,
which means that almost every open source supervision tool will understand those
status reports.

A custom route loader will generate one route per probe. In order to register those
routes, add into your `config/routes.yaml` the following code:

```yaml
monitoring_endpoint:
    resource: "@MonitoringBundle/Resources/config/routes/endpoint.yaml"
```

For those routes to respond, you need to generate an access token for users:

```sh
bin/console monitoring:generate-token
```

Follow the instructions on screen, it will display the new token and new
probes URL: you can copy/paste the newly generated token into your environment
variables:

```
MONITORING_TOKEN=your-generated-token
```

Note that you can run the command as many times as you wish in order to be
able to copy/paste probes URLs, **the command will never modify your**
**application configuration**.

If your site is protected by a firewall, you may add the following into
`config/packages/security.yaml`:

```yaml
security:
    firewalls:
        # Monitoring (access is token protected within controller)
        monitoring:
            pattern: ^/monitoring/status
            security: false
```

## Register admin report screen (optional)

A basic HTML report controller and template is provided, you may add it
into your `config/routes.yaml` configuration file:

```yaml
monitoring_admin:
    resource: "@MonitoringBundle/Resources/config/routes/admin.yaml"
    prefix: /admin
```

Please note that it is not security checked, you must manually configure your
firewall to protect it.

# Building your own probes and reports

## Build a simple probe

Implement the `\MakinaCorpus\Monitoring\Probe` interface.

```php

declare(strict_types=1);

namespace App\Monitoring;

use MakinaCorpus\Monitoring\Probe;
use MakinaCorpus\Monitoring\ProbeStatus;

/**
 * Collects number of items to process in queue, raise error when it is too much.
 */
final class QueueSizeProbe implements Probe
{
    /** @var ?int */
    private $warningThreshold;

    /** @var ?int */
    private $criticalThreshold;

    /** @var \My\Favourite\Database\Client */
    private $database;

    public function __construct(
        \My\Favourite\Database\Client $database,
        ?int $warningThreshold = null,
        ?int $criticalThreshold = null
    ) {
        $this->database = $database;
        $this->warningThreshold = $warningThreshold;
        $this->criticalThreshold = $criticalThreshold;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        // Internal name.
        return 'queue_size';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        // Human readable name, for reports.
        return "Queue size";
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): ProbeStatus
    {
        $queueSize = $this->database->query('SELECT COUNT(*) FROM "my_queue_table" WHERE "is_consumed" is false')->fetch();

        // For those who know Nagios or the like, just set a very short status
        // message intended for display purpose in larger reports.
        $message = \sprintf("my queue size: %d items", $queueSize);

        if ($queueSize >= $this->criticalThreshold) {
            return ProbeStatus::critical([$message, "queue is failing !"]);
        }
        if ($queueSize >= $this->warningThreshold) {
            return ProbeStatus::warning($message);
        }
        return ProbeStatus::ok($message);
    }
}
```

## Build a simple report generator

Implement the `\MakinaCorpus\Monitoring\InfoCollector` interface:

```php

declare(strict_types=1);

namespace App\Monitoring;

use MakinaCorpus\Monitoring\InfoCollector;
use MakinaCorpus\Monitoring\Output\CollectionBuilder;

/**
 * Collects information about all database tables.
 */
final class DataInfoCollector implements InfoCollector
{
    /** @var \My\Favourite\Database\Client */
    private $database;

    public function __construct(\My\Favourite\Database\Client $database)
    {
        $this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        // Internal name.
        return 'data';
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): iterable
    {
        // Tags will help build specific reports.
        return ['database', 'data', 'volume'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        // Human readable name, for reports.
        return "Data information";
    }

    /**
     * {@inheritdoc}
     */
    public function info(CollectionBuilder $builder): void
    {
        // Yes, this should work with pgsql.
        $rows = $this->database->query(<<<SQL
SELECT 
    pg_size_pretty(pg_total_relation_size(relid))
        AS "total size",
    pg_size_pretty(pg_table_size(relid))
        AS "table size",
    pg_size_pretty(pg_indexes_size(relid))
        AS "index size",
    concat(schemaname, '.', relname),
    concat('seq_scan: ',seq_scan, E'\\nseq_tup_read: ', seq_tup_read, E'\\nidx_scan: ', idx_scan, E'\\nidx_tup_fetch: ', idx_tup_fetch)
        AS "reads",
    concat('insert: ',n_tup_ins, E'\\nupdate: ', n_tup_upd, E'\\nhot_update: ', n_tup_hot_upd, E'\\ndelete: ', n_tup_del)
        AS "writes",
    concat('live: ',n_live_tup, E'\\ndead: ', n_dead_tup, E'\\nmod_since_analyze: ', n_mod_since_analyze)
        AS "state",
    concat('last_vacuum: ',last_vacuum, 'auto: ', last_autovacuum, E'\\nlast_analyze: ', last_analyze, ' auto: ', last_autoanalyze, E'\\ncpt_vacuum: ', vacuum_count, ' auto: ', autovacuum_count, E'\\ncpt_analyze: ', analyze_count, ' auto: ', autoanalyze_count)
        AS "vacuum"
    FROM pg_stat_user_tables
    ORDER BY pg_total_relation_size(relid) DESC
SQL
        );

        $table = $builder->addTable()->setHeaders([
            'table', 'total_size', // ...
        ]);

        foreach ($rows as $row) {
            $table->addRow([
                $row['relname'],
                $row['total size'],
                // ...
            ]);
        }
    }
}
```

## Combining both

Just implement both interfaces, they are compatible and won't conflict.

## Notes about reports and tags

Each `InfoCollector` implementation has a `getTags(): iterable` method, each tag
is a tag name string. Beware that you probably want to group associated reports
altogether for building your UI.

## Registering them into Symfony

For any probe or info collector class, register them into your container and add
the `monitoring_plugin` tag:

```yaml
services:

    # Considering that auto-wiring is enabled.
    _defaults:
        autowire: true

    App\Monitoring\:
        resource: '../src/Monitoring'
        tags: ['monitoring_plugin']
```

# Cron script

If you don't have a supervision tool to parse status endpoint, you can use
a simple check command run by a cron to perform status check.

## Configuring your cron

If you don't have any external software to read your probes, you can plug-in the
default (very naive) version of monitoring daemon into your crontab:

```crontab
# Every 10 minutes: sanity check.
# In real life, this is the job of your sysadmin to choose recurrence.
*/10 * * * * /path/to/your/symfony/app/bin/console monitoring:check
```

## Pluging reactions to errors

Per default, the check command won't do anything, you must manually write handlers
for reacting upon broken probes.

```php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use MakinaCorpus\Monitoring\Event\ProbeResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @codeCoverageIgnore
 */
final class MonitoringEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdo}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProbeResultEvent::class => 'onProbeResult',
        ];
    }

    public function onProbeResult(ProbeResultEvent $event)
    {
        if ($event->isCritical()) {
            // Do something.
        } else if ($event->isMalfunctioning()) {
            // Do something else.
        }
    }
}
```

# Fetching nagios-compatible result using the command line

```sh
/path/to/your/symfony/app/bin/console monitoring:status
```

This will output a Nagios parser compatible text.
