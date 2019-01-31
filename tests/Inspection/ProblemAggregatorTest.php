<?php

namespace Scheb\InspectionConverter\Test\Inspection;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Inspection\ProblemAggregator;
use Scheb\InspectionConverter\Inspection\ProblemIterator;
use Scheb\InspectionConverter\Inspection\ProblemIteratorFactory;
use Scheb\InspectionConverter\Test\TestCase;

class ProblemAggregatorTest extends TestCase
{
    private const PROJECT_PATH = '/project/path';

    /**
     * @var ProblemIteratorFactory|MockObject
     */
    private $problemIteratorFactory;

    protected function setUp()
    {
        $this->problemIteratorFactory = $this->createMock(ProblemIteratorFactory::class);
    }

    private function createProblemAggregator(array $ignoreInspections, array $ignoreFiles, array $ignoreMessages): ProblemAggregator
    {
        return new ProblemAggregator($this->problemIteratorFactory, $ignoreInspections, $ignoreFiles, $ignoreMessages);
    }

    /**
     * @return MockObject|ProblemIterator
     */
    private function createProblemIterator(array $problems): MockObject
    {
        $iterator = $this->createMock(ProblemIterator::class);
        $iterator
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($problems));

        return $iterator;
    }

    private function stubProblemIteratorFactoryCreatesIterator(array $inspectionIterators): void
    {
        $map = [];
        foreach ($inspectionIterators as $inspection => $iterator) {
            $map[] = [$inspection, self::PROJECT_PATH, $iterator];
        }

        $this->problemIteratorFactory
            ->expects($this->any())
            ->method('createProblemIterator')
            ->willReturnMap($map);
    }

    private function createProblem(string $file, string $message): MockObject
    {
        $problem = $this->createMock(Problem::class);
        $problem->expects($this->any())->method('getFileName')->willReturn($file);
        $problem->expects($this->any())->method('getDescription')->willReturn($message);

        return $problem;
    }

    private function stubInspectionsHaveProblems(): void
    {
        $problemIterator1 = $this->createProblemIterator([
            $this->createProblem('file1', 'Message1.1'),
            $this->createProblem('file2', 'Message2'),
        ]);
        $problemIterator2 = $this->createProblemIterator([
            $this->createProblem('file1', 'Message1.2'),
            $this->createProblem('file3', 'Message3'),
        ]);
        $this->stubProblemIteratorFactoryCreatesIterator([
            'Inspection1.xml' => $problemIterator1,
            'Inspection2.xml' => $problemIterator2,
        ]);
    }

    /**
     * @test
     */
    public function readInspections_ignoredInspections_returnAllProblemsExceptInspection(): void
    {
        $this->problemIteratorFactory
            ->expects($this->never())
            ->method('createProblemIterator');

        $aggregator = $this->createProblemAggregator(['Inspection1'], [], []);
        $returnValue = $aggregator->readInspections(['Inspection1.xml'], self::PROJECT_PATH);
        $this->assertCount(0, $returnValue->getProblemsByFile());
    }

    /**
     * @test
     */
    public function readInspections_givenInspectionFiles_createAggregate(): void
    {
        $this->stubInspectionsHaveProblems();
        $aggregator = $this->createProblemAggregator([], [], []);
        $returnValue = $aggregator->readInspections(['Inspection1.xml', 'Inspection2.xml'], self::PROJECT_PATH);

        $problemsPerFile = $returnValue->getProblemsByFile();
        $this->assertArrayHasKey('file1', $problemsPerFile);
        $this->assertCount(2, $problemsPerFile['file1']);
        $this->assertArrayHasKey('file2', $problemsPerFile);
        $this->assertCount(1, $problemsPerFile['file2']);
        $this->assertArrayHasKey('file3', $problemsPerFile);
        $this->assertCount(1, $problemsPerFile['file3']);
    }

    /**
     * @test
     */
    public function readInspections_ignoreFile1_returnAllProblemsExceptFile1(): void
    {
        $this->stubInspectionsHaveProblems();
        $aggregator = $this->createProblemAggregator([], ['file1'], []);
        $returnValue = $aggregator->readInspections(['Inspection1.xml', 'Inspection2.xml'], self::PROJECT_PATH);

        $problemsPerFile = $returnValue->getProblemsByFile();
        $this->assertArrayNotHasKey('file1', $problemsPerFile);

        $this->assertArrayHasKey('file2', $problemsPerFile);
        $this->assertCount(1, $problemsPerFile['file2']);
        $this->assertArrayHasKey('file3', $problemsPerFile);
        $this->assertCount(1, $problemsPerFile['file3']);
    }

    /**
     * @test
     */
    public function readInspections_ignoreMessage1_returnAllProblemsExceptFile1(): void
    {
        $this->stubInspectionsHaveProblems();
        $aggregator = $this->createProblemAggregator([], [], ['Message1']);
        $returnValue = $aggregator->readInspections(['Inspection1.xml', 'Inspection2.xml'], self::PROJECT_PATH);

        $problemsPerFile = $returnValue->getProblemsByFile();
        $this->assertArrayNotHasKey('file1', $problemsPerFile);

        $this->assertArrayHasKey('file2', $problemsPerFile);
        $this->assertCount(1, $problemsPerFile['file2']);
        $this->assertArrayHasKey('file3', $problemsPerFile);
        $this->assertCount(1, $problemsPerFile['file3']);
    }
}
