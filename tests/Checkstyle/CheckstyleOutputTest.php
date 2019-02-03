<?php

namespace Scheb\Inspection\Converter\Test\Checkstyle;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\Inspection\Converter\Checkstyle\CheckstyleGenerator;
use Scheb\Inspection\Converter\Checkstyle\CheckstyleOutput;
use Scheb\Inspection\Converter\Test\TestCase;
use Scheb\Inspection\Core\FileSystem\FileWriter;
use Scheb\Inspection\Core\Inspection\Problem;
use Scheb\Inspection\Core\Inspection\ProblemSummary;

class CheckstyleOutputTest extends TestCase
{
    /**
     * @test
     */
    public function outputCheckstyle_problemsPerFileGiven_writeSortedCheckstyle(): void
    {
        $problemFile1Line1 = $this->stubProblemAtLine(1);
        $problemFile1Line2 = $this->stubProblemAtLine(2);
        $problemFile2Line3 = $this->stubProblemAtLine(3);
        $problemFile2Line4 = $this->stubProblemAtLine(4);
        $problemsPerFile = [
            'file2' => [
                $problemFile2Line3,
                $problemFile2Line4,
            ],
            'file1' => [
                $problemFile1Line2,
                $problemFile1Line1,
            ],
        ];
        $problemSummary = $this->createMock(ProblemSummary::class);
        $problemSummary->expects($this->any())->method('getProblemsByFile')->willReturn($problemsPerFile);

        $checkstyleGenerator = $this->createMock(CheckstyleGenerator::class);
        $checkstyleGenerator->expects($this->once())->method('getHeader')->willReturn('header');
        $checkstyleGenerator->expects($this->once())->method('getFooter')->willReturn('footer');
        $checkstyleGenerator
            ->expects($this->exactly(2))
            ->method('generateFileXmlElement')
            ->withConsecutive(
                ['file1', [$problemFile1Line1, $problemFile1Line2]],
                ['file2', [$problemFile2Line3, $problemFile2Line4]]
            )
            ->willReturnOnConsecutiveCalls('cs1', 'cs2');

        $fileWriter = $this->createMock(FileWriter::class);
        $this->expectWrites($fileWriter, ["header\n", "cs1\n", "cs2\n", "footer\n"]);

        $output = new CheckstyleOutput($checkstyleGenerator, $fileWriter);
        $output->outputCheckstyle($problemSummary);
    }

    private function stubProblemAtLine(int $line): MockObject
    {
        $problem = $this->createMock(Problem::class);
        $problem
            ->expects($this->once())
            ->method('getLine')
            ->willReturn($line);

        return $problem;
    }

    private function expectWrites(MockObject $fileWriter, array $writeValues): void
    {
        $fileWriter
            ->expects($this->exactly(count($writeValues)))
            ->method('write')
            ->withConsecutive(...array_map(function ($value) { return [$value]; }, $writeValues));
    }
}
