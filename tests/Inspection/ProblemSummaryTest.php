<?php

namespace Scheb\InspectionConverter\Test\Inspection;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Inspection\ProblemSummary;
use Scheb\InspectionConverter\Test\TestCase;

class ProblemSummaryTest extends TestCase
{
    /**
     * @var ProblemSummary
     */
    private $problemSummary;

    protected function setUp()
    {
        $this->problemSummary = new ProblemSummary();
        $this->problemSummary->addProblem('file1', $this->createProblem());
        $this->problemSummary->addProblem('file2', $this->createProblem());
        $this->problemSummary->addProblem('file1', $this->createProblem());
    }

    /**
     * @return Problem|MockObject
     */
    private function createProblem(): MockObject
    {
        return $this->createMock(Problem::class);
    }

    /**
     * @test
     */
    public function getProblemsByFile_3ProblemsAdded_returnPerFile(): void
    {
        $problemsByFile = $this->problemSummary->getProblemsByFile();

        $this->assertArrayHasKey('file1', $problemsByFile);
        $this->assertCount(2, $problemsByFile['file1']);

        $this->assertArrayHasKey('file2', $problemsByFile);
        $this->assertCount(1, $problemsByFile['file2']);
    }

    /**
     * @test
     */
    public function getNumProblems_problemsAdded_returnProblemCount(): void
    {
        $this->assertEquals(3, $this->problemSummary->getNumProblems());
    }

    /**
     * @test
     */
    public function getNumProblems_problemsAdded_returnFilesCount(): void
    {
        $this->assertEquals(2, $this->problemSummary->getNumFiles());
    }
}
