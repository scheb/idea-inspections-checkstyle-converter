<?php

namespace Scheb\InspectionConverter\Cli;

use Scheb\InspectionConverter\Checkstyle\CheckstyleOutput;
use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Inspection\ProblemIterator;
use Symfony\Component\Console\Command\Command as AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends AbstractCommand
{
    public const ARG_PROJECT_PATH = 'projectPath';
    public const ARG_INSPECTIONS_FOLDER = 'inspectionsFolder';
    public const ARG_CHECKSTYLE_OUTPUT = 'checkstyleOutput';

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
            ->addArgument(self::ARG_PROJECT_PATH, InputArgument::REQUIRED, 'Path to the project root')
            ->addArgument(self::ARG_INSPECTIONS_FOLDER, InputArgument::REQUIRED, 'Folder with the inspections XML files')
            ->addArgument(self::ARG_CHECKSTYLE_OUTPUT, InputArgument::REQUIRED, 'Folder with the inspections XML files');
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
        $projectPath = realpath($this->input->getArgument(self::ARG_PROJECT_PATH));

        $inspectionsFolder = realpath($this->input->getArgument(self::ARG_INSPECTIONS_FOLDER));
        if (false === $inspectionsFolder || !is_dir($inspectionsFolder)) {
            throw new \InvalidArgumentException('Argument "'.self::ARG_INSPECTIONS_FOLDER.'" must be a path to a folder');
        }

        $checkstyleFile = $this->input->getArgument(self::ARG_CHECKSTYLE_OUTPUT);
        $checkstyleFileDir = realpath(dirname($checkstyleFile));
        $checkstyleFileRealPath = realpath($checkstyleFile);

        if ($checkstyleFileDir === false || !is_writeable($checkstyleFileDir) || ($checkstyleFileRealPath !== false && !is_writeable($checkstyleFileRealPath))) {
            throw new \InvalidArgumentException('Argument "'.self::ARG_CHECKSTYLE_OUTPUT.'" must be a path to writable file');
        }

        $inspectionsFiles = $this->findXmlFiles($inspectionsFolder);
        $problemsByFile = $this->readInspections($inspectionsFiles, $projectPath);
        $this->writeCheckstyle($problemsByFile, $checkstyleFile);
    }

    private function findXmlFiles(string $inspectionsFolder): array
    {
        $inspectionsFiles = glob($inspectionsFolder.'/*.xml');
        $this->output->writeln('Found '.count($inspectionsFiles).' inspection files');
        $this->output->writeln('');

        return $inspectionsFiles;
    }

    private function readInspections(array $inspectionsFiles, string $projectPath): array
    {
        $this->output->write('Read inspections ...');
        $numFiles = 0;
        $numProblems = 0;
        $problemsByFile = [];
        foreach ($inspectionsFiles as $inspectionsFile) {
            $problems = ProblemIterator::create($inspectionsFile, $projectPath);
            /** @var Problem $problem */
            foreach ($problems as $problem) {
                $filename = $problem->getFilename();
                if (!isset($problemsByFile[$filename])) {
                    $problemsByFile[$filename] = [];
                    ++$numFiles;
                }
                $problemsByFile[$filename][] = $problem;
                ++$numProblems;
            }
        }
        $this->output->writeln(' Done');
        $this->output->writeln(sprintf('Found %s results in %s files', $numProblems, $numFiles));
        $this->output->writeln('');

        return $problemsByFile;
    }

    private function writeCheckstyle(array $problemsByFile, string $checkstyleFile): void
    {
        $this->output->write('Write checkstyle file ...');
        $checkstyleOutput = CheckstyleOutput::create($checkstyleFile);
        $checkstyleOutput->outputCheckstyle($problemsByFile);
        $this->output->writeln(' Done');
    }
}
