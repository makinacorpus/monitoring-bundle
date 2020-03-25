<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Command;

use MakinaCorpus\Monitoring\InfoCollector;
use MakinaCorpus\Monitoring\ProbeRegistry;
use MakinaCorpus\Monitoring\Formatter\HtmlOutputFormatter;
use MakinaCorpus\Monitoring\Formatter\PlainTextOutputFormatter;
use MakinaCorpus\Monitoring\Formatter\StandardReportFormatter;
use MakinaCorpus\Monitoring\Output\CollectionBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ReportCommand extends Command
{
    protected static $defaultName = 'monitoring:report';

    /** @var ProbeRegistry */
    private $probeRegistry;

    /**
     * Default constructor
     */
    public function __construct(ProbeRegistry $probeRegistry)
    {
        parent::__construct();

        $this->probeRegistry = $probeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setAliases(['montoring:report'])
            ->setDescription('Collect information about system status and display it')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, "Format, can be 'plain' or 'standard'", 'plain')
            ->addArgument('tags', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, "Which tags to fetch, if none given, all are run")
        ;
    }

    /**
     * {@inheritdoc}
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress PossiblyInvalidArgument
     *   Cause unsuppressable warnings with \sprintf()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = (array)$input->getArgument('tags');
        if (!$tags) {
            $tags = \array_keys($this->probeRegistry->getInfoCollectorTags());
        }

        switch ($format = $input->getOption('format')) {
            case 'plain':
                $formatter = new PlainTextOutputFormatter();
                break;
            case 'html':
                $formatter = new HtmlOutputFormatter();
                break;
            default:
                throw new \InvalidArgumentException(\sprintf("'%s': unknown output format", $format));
        }

        $topLevelBuilder = new CollectionBuilder();
        foreach ($tags as $tag) {
            $tagBuilder = new CollectionBuilder($this->probeRegistry->getTagLabel($tag));
            if ($probes = $this->probeRegistry->getInfoCollectorsWithTag($tag)) {
                 foreach ($probes as $probe) {
                    \assert($probe instanceof InfoCollector);
                    $builder = new CollectionBuilder($probe->getTitle());
                    $probe->info($builder);
                    $tagBuilder->add($builder->create());
                }
            }
            $topLevelBuilder->add($tagBuilder->create());
        }

        $reportFormatter = new StandardReportFormatter();
        $output->writeln($reportFormatter->format($formatter, $topLevelBuilder->create()));
    }
}
