<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Command;

use MakinaCorpus\Monitoring\Probe;
use MakinaCorpus\Monitoring\ProbeRegistry;
use MakinaCorpus\Monitoring\ProbeStatus;
use MakinaCorpus\Monitoring\Event\ProbeResultEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CheckCommand extends Command
{
    protected static $defaultName = 'monitoring:check';

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProbeRegistry */
    private $probeRegistry;

    /**
     * Default constructor
     */
    public function __construct(ProbeRegistry $probeRegistry, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        parent::__construct();

        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->probeRegistry = $probeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setAliases(['montoring:check'])
            ->setDescription('Run all probes locally, and raise events on which local code can react')
        ;
    }

    /**
     * {@inheritdoc}
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress InvalidReturnType
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->probeRegistry->getAllProbes() as $name => $probe) {
            \assert($probe instanceof Probe);
            try {
                $status = $probe->getStatus();

                switch ($status->getStatusCode()) {

                    case ProbeStatus::RESULT_OK:
                        $this->logger->info(\sprintf("Probe '%s' OK with messages: %s", $name, \implode(', ', $status->getMessages())));
                        break;

                    case ProbeStatus::RESULT_WARNING:
                        $this->logger->warning(\sprintf("Probe '%s' WARNING with messages: %s", $name, \implode(', ', $status->getMessages())));
                        break;

                    case ProbeStatus::RESULT_CRITICAL:
                        $this->logger->critical(\sprintf("Probe '%s' CRITICAL with messages: %s", $name, \implode(', ', $status->getMessages())));
                        break;

                    case ProbeStatus::RESULT_UNKNOWN:
                        $this->logger->critical(\sprintf("Probe '%s' UNKNOWN with messages: %s", $name, \implode(', ', $status->getMessages())));
                        break;
                }

                $this->eventDispatcher->dispatch(new ProbeResultEvent($name, $status));

            } catch (\Throwable $e) {
                if ($output->isDebug()) {
                    throw $e;
                }
                $this->logger->critical(\sprintf("Probe '%s' failed with message: %s", $e->getMessage()));
                $this->eventDispatcher->dispatch(new ProbeResultEvent($name, ProbeStatus::unknownFromException($e)));
            }
        }
        return 0;
    }
}
