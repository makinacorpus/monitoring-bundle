<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Bridge\Symfony\Controller;

use MakinaCorpus\Monitoring\InfoCollector;
use MakinaCorpus\Monitoring\Probe;
use MakinaCorpus\Monitoring\ProbeRegistry;
use MakinaCorpus\Monitoring\Formatter\HtmlOutputFormatter;
use MakinaCorpus\Monitoring\Formatter\StandardProbeFormatter;
use MakinaCorpus\Monitoring\Formatter\StandardReportFormatter;
use MakinaCorpus\Monitoring\Output\CollectionBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MonitoringController extends AbstractController
{
    /** @var bool */
    private $debug = false;

    /** @var ?string */
    private $token;

    /** @var string */
    private $reportTemplateName;

    public function __construct(string $reportTemplateName, ?string $token, bool $debug = false)
    {
        $this->debug = $debug;
        $this->reportTemplateName = $reportTemplateName;
        $this->token = $token;
    }

    /**
     * HTML human readable report.
     */
    public function report(ProbeRegistry $probeRegistry, string $tag)
    {
        $content = '';
        $formatter = new HtmlOutputFormatter();

        $tagBuilder = new CollectionBuilder();
        if ($probes = $probeRegistry->getInfoCollectorsWithTag($tag)) {
             foreach ($probes as $probe) {
                \assert($probe instanceof InfoCollector);
                $builder = new CollectionBuilder($probe->getTitle());
                $probe->info($builder);
                $tagBuilder->add($builder->create());
            }
        }

        $reportFormatter = new StandardReportFormatter();
        $content = $reportFormatter->format($formatter, $tagBuilder->create(), 2);

        return $this->render(
            $this->reportTemplateName,
            [
                'report' => $content,
                'tag' => $tag,
                'title' => $probeRegistry->getTagLabel($tag),
            ]
        );
    }

    /**
     * Ensure token access on status reports.
     */
    private function checkToken(Request $request): void
    {
        if (!$this->token || !($token = $request->get('token')) || $token !== $this->token) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * Single probe status endpoint.
     */
    public function status(Request $request, ProbeRegistry $probeRegistry, string $name)
    {
        $this->checkToken($request);

        try {
            $probe = $probeRegistry->getProbe($name);
            $formatter = new StandardProbeFormatter();

            try {
                $content = $formatter->format($probe->getStatus(), $probe->getName());
            } catch (\Throwable $e) {
                if ($this->debug) {
                    throw $e;
                }
                $content = $formatter->formatException($e);
            }

            return new Response($content, 200, ['Content-Type' => 'text/plain']);

        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('Not Found', $e);
        }
    }

    /**
     * All probes status endpoint.
     */
    public function statusAll(Request $request, ProbeRegistry $probeRegistry)
    {
        $this->checkToken($request);

        $content = '';
        $formatter = new StandardProbeFormatter();

        foreach ($probeRegistry->getAllProbes() as $probe) {
            \assert($probe instanceof Probe);
            try {
                $content .= $formatter->format($probe->getStatus(), $probe->getName())."\n";
            } catch (\Throwable $e) {
                if ($this->debug) {
                    throw $e;
                }
                $content .= $formatter->formatException($e, $probe->getName());
            }
        }

        return new Response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
