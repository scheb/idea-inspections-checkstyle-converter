<?php

namespace Scheb\Inspection\Converter\Cli;

use Scheb\Inspection\Converter\Checkstyle\CheckstyleOutput;
use Scheb\Inspection\Converter\Checkstyle\SeverityMapping;
use Scheb\Inspection\Core\Inspection\ProblemAggregator;
use Scheb\Inspection\Core\Inspection\ProblemFactory;
use Scheb\Inspection\Core\Inspection\ProblemIteratorFactory;
use Scheb\Inspection\Core\Inspection\ProblemSummary;
use Symfony\Component\Console\Command\Command as AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends AbstractCommand
{
    public const ARG_INSPECTIONS_FOLDER = 'inspectionsFolder';
    public const ARG_CHECKSTYLE_OUTPUT_FILE = 'checkstyleOutputFile';
    public const OPT_PROJECT_ROOT = 'projectRoot';
    public const OPT_IGNORE_INSPECTION = 'ignoreInspection';
    public const OPT_IGNORE_MESSAGE = 'ignoreMessage';
    public const OPT_IGNORE_FILE = 'ignoreFile';
    public const OPT_IGNORE_SEVERITY = 'ignoreSeverity';
    public const OPT_MAP_SEVERITY = 'mapSeverity';
    public const OPT_DEFAULT_SEVERITY = 'defaultSeverity';

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('convert')
            ->addArgument(self::ARG_INSPECTIONS_FOLDER, InputArgument::REQUIRED, 'Folder with the inspections XML files')
            ->addArgument(self::ARG_CHECKSTYLE_OUTPUT_FILE, InputArgument::REQUIRED, 'Checkstyle file to be written')
            ->addOption(self::OPT_PROJECT_ROOT, 'r', InputOption::VALUE_REQUIRED, 'Path to the project root', '')
            ->addOption(self::OPT_IGNORE_INSPECTION, 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Ignore inspections matching the regex pattern')
            ->addOption(self::OPT_IGNORE_MESSAGE, 'm', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Ignore messages matching the regex pattern')
            ->addOption(self::OPT_IGNORE_FILE, 'f', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Ignore files matching the regex pattern')
            ->addOption(self::OPT_IGNORE_SEVERITY, 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Ignore severities (exact match)')
            ->addOption(self::OPT_MAP_SEVERITY, 'S', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Map severity from to, format "input:output"')
            ->addOption(self::OPT_DEFAULT_SEVERITY, 'D', InputOption::VALUE_REQUIRED, 'Used in combination with '.self::OPT_MAP_SEVERITY.' to define the default severity');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        try {
            $this->doExecute();
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());

            return $e->getCode() ?: 1;
        }

        return 0;
    }

    private function doExecute(): void
    {
        $projectRoot = $this->input->getOption(self::OPT_PROJECT_ROOT);
        $ignoreInspections = $this->input->getOption(self::OPT_IGNORE_INSPECTION) ?? [];
        $ignoreFiles = $this->input->getOption(self::OPT_IGNORE_FILE) ?? [];
        $ignoreMessages = $this->input->getOption(self::OPT_IGNORE_MESSAGE) ?? [];
        $ignoreSeverities = $this->input->getOption(self::OPT_IGNORE_SEVERITY) ?? [];
        $severityMapping = $this->getSeverityMapping();

        $inspectionsFolder = realpath($this->input->getArgument(self::ARG_INSPECTIONS_FOLDER));
        if (false === $inspectionsFolder || !is_dir($inspectionsFolder)) {
            throw new \InvalidArgumentException('Argument "'.self::ARG_INSPECTIONS_FOLDER.'" must be a path to a folder');
        }

        $checkstyleFile = $this->input->getArgument(self::ARG_CHECKSTYLE_OUTPUT_FILE);
        $checkstyleFileDir = realpath(dirname($checkstyleFile));
        $checkstyleFileRealPath = realpath($checkstyleFile);

        if (
            false === $checkstyleFileDir // Output dir doesn't exist
            || !is_writeable($checkstyleFileDir) // Output dir not writable
            || (false !== $checkstyleFileRealPath && (is_dir($checkstyleFileRealPath) || !is_writeable($checkstyleFileRealPath))) // Output file exists, but is a dir or not writable
        ) {
            throw new \InvalidArgumentException('Argument "'.self::ARG_CHECKSTYLE_OUTPUT_FILE.'" must be a path to writable file');
        }

        $inspectionsFiles = $this->findXmlFiles($inspectionsFolder);
        $problemsByFile = $this->readInspections($inspectionsFiles, $projectRoot, $ignoreInspections, $ignoreFiles, $ignoreMessages, $ignoreSeverities);
        $this->writeCheckstyle($problemsByFile, $severityMapping, $checkstyleFile);
    }

    private function getSeverityMapping(): SeverityMapping
    {
        $severityMapOptions = $this->input->getOption(self::OPT_MAP_SEVERITY) ?? [];
        $severityMap = [];
        foreach ($severityMapOptions as $severityMapOption) {
            $inOut = explode(':', $severityMapOption, 2);
            if (!count($inOut) == 2) {
                throw new \InvalidArgumentException('Option "'.self::OPT_MAP_SEVERITY.'" must be format "input:output"');
            }
            $severityMap[$inOut[0]] = $inOut[1];
        }

        return new SeverityMapping($severityMap, $this->input->getOption(self::OPT_DEFAULT_SEVERITY));
    }

    private function findXmlFiles(string $inspectionsFolder): array
    {
        $inspectionsFiles = glob($inspectionsFolder.'/*.xml');
        $this->output->writeln('Found '.count($inspectionsFiles).' inspection files');
        $this->output->writeln('');

        return $inspectionsFiles;
    }

    private function readInspections(array $inspectionsFiles, string $projectRoot, array $ignoreInspections, array $ignoreFiles, array $ignoreMessages, $ignoreSeverities): ProblemSummary
    {
        $this->output->write('Read inspections ...');

        $aggregator = new ProblemAggregator(new ProblemIteratorFactory($ignoreInspections, $ignoreFiles, $ignoreMessages, $ignoreSeverities), new ProblemFactory());
        $summary = $aggregator->readInspections($inspectionsFiles, $projectRoot);

        $this->output->writeln(' Done');
        $this->output->writeln(sprintf('Found %s results in %s files', $summary->getNumProblems(), $summary->getNumFiles()));
        $this->output->writeln('');

        return $summary;
    }

    private function writeCheckstyle(ProblemSummary $problemsByFile, SeverityMapping $severityMapping, string $checkstyleFile): void
    {
        $this->output->write('Write checkstyle file ...');
        $checkstyleOutput = CheckstyleOutput::create($severityMapping, $checkstyleFile);
        $checkstyleOutput->outputCheckstyle($problemsByFile);
        $this->output->writeln(' Done');
    }
}
