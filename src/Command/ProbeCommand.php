<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Command;

use MakinaCorpus\Monitoring\Probe;
use MakinaCorpus\Monitoring\ProbeRegistry;
use MakinaCorpus\Monitoring\Formatter\StandardProbeFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ProbeCommand extends Command
{
    protected static $defaultName = 'monitoring:status';

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
            ->setAliases(['montoring:status'])
            ->setDescription('Run probes and output Nagios-compatible status text')
            ->addArgument('name', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, "Which probe(s) to run, if none given, all are run")
        ;
    }

    /**
     * {@inheritdoc}
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress InvalidReturnType
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $names = (array)$input->getArgument('name');
        $formatter = new StandardProbeFormatter();

        foreach ($this->probeRegistry->getAllProbes() as $probe) {
            \assert($probe instanceof Probe);
            if (!$names || \in_array($probe->getName(), $names)) {
                try {
                    $output->writeln(
                        $formatter->format(
                            $probe->getStatus(),
                            $probe->getName()
                        )
                    );
                } catch (\Throwable $e) {
                    if ($output->isDebug() || $output->isVeryVerbose()) {
                        throw $e;
                    }
                    $formatter->formatException($e, $probe->getName());
                }
            }
        }

        return 0;
    }
}
