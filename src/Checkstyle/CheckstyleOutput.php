<?php

namespace Scheb\InspectionConverter\Checkstyle;

use Scheb\InspectionConverter\FileSystem\FileWriter;
use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Inspection\ProblemSummary;

class CheckstyleOutput
{
    /**
     * @var CheckstyleGenerator
     */
    private $checkstyleGenerator;

    /**
     * @var FileWriter
     */
    private $fileWriter;

    public function __construct(CheckstyleGenerator $checkstyleGenerator, FileWriter $fileWriter)
    {
        $this->checkstyleGenerator = $checkstyleGenerator;
        $this->fileWriter = $fileWriter;
    }

    public static function create(string $outputFile): self
    {
        return new self(new CheckstyleGenerator(), new FileWriter($outputFile));
    }

    public function outputCheckstyle(ProblemSummary $problemSummary): void
    {
        $problemsByFile = $problemSummary->getProblemsByFile();
        ksort($problemsByFile);
        $this->fileWriter->write($this->checkstyleGenerator->getHeader()."\n");
        foreach ($problemsByFile as $fileName => $problems) {
            $problems = $this->sortProblemsByLine($problems);
            $xml = $this->checkstyleGenerator->generateFileXmlElement($fileName, $problems);
            $this->fileWriter->write($xml."\n");
        }
        $this->fileWriter->write($this->checkstyleGenerator->getFooter()."\n");
    }

    private function sortProblemsByLine(array $fileProblems): array
    {
        usort($fileProblems, function (Problem $a, Problem $b) {
            return $a->getLine() <=> $b->getLine();
        });

        return $fileProblems;
    }
}
