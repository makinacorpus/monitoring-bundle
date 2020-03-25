<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony\Command;

use MakinaCorpus\Monitoring\ProbeRegistry;
use MakinaCorpus\Monitoring\Bridge\Symfony\Routing\ProbeRouteLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GenerateTokenCommand extends Command
{
    protected static $defaultName = 'monitoring:generate-token';

    /** @var string */
    private $currentToken;

    /** @var ProbeRegistry */
    private $probeRegistry;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(ProbeRegistry $probeRegistry, UrlGeneratorInterface $urlGenerator, ?string $currentToken)
    {
        parent::__construct();

        $this->currentToken = $currentToken ?? 'ERR_NO_TOKEN';
        $this->probeRegistry = $probeRegistry;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setAliases(['montoring:generate-token']);
        $this->setDescription('Generate a token');
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
        $token = \bin2hex(\random_bytes(32));

        $output->writeln("Current: ".$this->currentToken);
        $output->writeln("Newly generated token: ".$token."\n");

        $newUrl = $this->generateAllUrlAsListString($token);
        $currentUrl = $this->generateAllUrlAsListString($this->currentToken);

        $output->writeln(<<<EOT
Please set the following environment variable:

    MONITORING_TOKEN={$token}

Either in your .env file, either in your project runtime environment.
Then give the following URL to your supervision system administrator
for collecting probes status.

Newly generated URL:

{$newUrl}

Current URL:

{$currentUrl}
EOT
        );

        $output->writeln("");
    }

    /**
     * Generate all URLs as a string
     */
    private function generateAllUrlAsListString(string $token): string
    {
        $ret = [];
        $indent = \str_repeat(" ", 4);

        $ret[] = $this->urlGenerator->generate(
            'monitoring_status',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        foreach ($this->probeRegistry->getAllProbeNames() as $name) {
            $ret[] = $this->urlGenerator->generate(
                ProbeRouteLoader::getProbeRoute($name),
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        return $indent.\implode("\n".$indent, $ret);
    }
}
