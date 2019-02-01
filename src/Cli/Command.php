<?php

namespace Scheb\InspectionConverter\Cli;

use Scheb\InspectionConverter\Checkstyle\CheckstyleOutput;
use Scheb\InspectionConverter\Inspection\ProblemAggregator;
use Scheb\InspectionConverter\Inspection\ProblemIteratorFactory;
use Scheb\InspectionConverter\Inspection\ProblemSummary;
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
            ->addArgument(self::ARG_CHECKSTYLE_OUTPUT_FILE, InputArgument::REQUIRED, 'Folder with the inspections XML files')
            ->addOption(self::OPT_PROJECT_ROOT, 'r', InputOption::VALUE_REQUIRED, 'Path to the project root', '')
            ->addOption(self::OPT_IGNORE_INSPECTION, 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Ignore inspections matching the regex pattern')
            ->addOption(self::OPT_IGNORE_MESSAGE, 'm', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Ignore messages matching the regex pattern')
            ->addOption(self::OPT_IGNORE_FILE, 'f', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Ignore files matching the regex pattern');
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
        $problemsByFile = $this->readInspections($inspectionsFiles, $projectRoot, $ignoreInspections, $ignoreFiles, $ignoreMessages);
        $this->writeCheckstyle($problemsByFile, $checkstyleFile);
    }

    private function findXmlFiles(string $inspectionsFolder): array
    {
        $inspectionsFiles = glob($inspectionsFolder.'/*.xml');
        $this->output->writeln('Found '.count($inspectionsFiles).' inspection files');
        $this->output->writeln('');

        return $inspectionsFiles;
    }

    private function readInspections(array $inspectionsFiles, string $projectRoot, array $ignoreInspections, array $ignoreFiles, array $ignoreMessages): ProblemSummary
    {
        $this->output->write('Read inspections ...');

        $aggregator = new ProblemAggregator(new ProblemIteratorFactory(), $ignoreInspections, $ignoreFiles, $ignoreMessages);
        $summary = $aggregator->readInspections($inspectionsFiles, $projectRoot);

        $this->output->writeln(' Done');
        $this->output->writeln(sprintf('Found %s results in %s files', $summary->getNumProblems(), $summary->getNumFiles()));
        $this->output->writeln('');

        return $summary;
    }

    private function writeCheckstyle(ProblemSummary $problemsByFile, string $checkstyleFile): void
    {
        $this->output->write('Write checkstyle file ...');
        $checkstyleOutput = CheckstyleOutput::create($checkstyleFile);
        $checkstyleOutput->outputCheckstyle($problemsByFile);
        $this->output->writeln(' Done');
    }
}
