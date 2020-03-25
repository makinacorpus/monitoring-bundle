<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Goat\Query;

use Goat\Runner\Runner;
use MakinaCorpus\Monitoring\InfoCollector;
use MakinaCorpus\Monitoring\Output\CollectionBuilder;

/**
 * Display advanced information about all database tables.
 */
final class PgSQLSchemaInfoCollector implements InfoCollector
{
    /** @var Runner */
    private $runner;

    public function __construct(Runner $runner)
    {
        $this->runner = $runner;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'database_schema';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return "Schema";
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): iterable
    {
        return ['database'];
    }

    /**
     * Get information
     */
    public function info(CollectionBuilder $builder): void
    {
        $table = $builder->addTable("Table statistics and data volume");
        $table->setHeaders([
            'total size',
            'table size',
            'index size',
            'table name',
            'reads',
            'writes',
            'state',
            'vacuum',
        ]);

        $timer = -\microtime(true);
        $results = $this
            ->runner
            ->execute(<<<SQL
SELECT 
    pg_size_pretty(pg_total_relation_size(relid)) as "total size",
    pg_size_pretty(pg_table_size(relid)) as "table size",
    pg_size_pretty(pg_indexes_size(relid)) as "index size",
    concat(schemaname, '.', relname),
    concat('seq_scan: ',seq_scan, E'\\nseq_tup_read: ', seq_tup_read, E'\\nidx_scan: ', idx_scan, E'\\nidx_tup_fetch: ', idx_tup_fetch) as "reads",
    concat('insert: ',n_tup_ins, E'\\nupdate: ', n_tup_upd, E'\\nhot_update: ', n_tup_hot_upd, E'\\ndelete: ', n_tup_del) as "writes",
    concat('live: ',n_live_tup, E'\\ndead: ', n_dead_tup, E'\\nmod_since_analyze: ', n_mod_since_analyze) as "state",
    concat('last_vacuum: ', last_vacuum, 'auto: ', last_autovacuum, E'\\nlast_analyze: ', last_analyze, ' auto: ', last_autoanalyze, E'\\ncpt_vacuum: ', vacuum_count, ' auto: ', autovacuum_count, E'\\ncpt_analyze: ', analyze_count, ' auto: ', autoanalyze_count) as "vacuum"
  FROM pg_stat_user_tables
  ORDER BY pg_total_relation_size(relid) DESC;
SQL
            )
        ;
        $timer += \microtime(true);

        while ($row = $results->fetch()) {
            $table->addRow($row);
        }

        $table->setExecutionTime($timer);
    }
}
