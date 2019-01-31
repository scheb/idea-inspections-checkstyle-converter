<?php

namespace Scheb\InspectionConverter\Test\Inspection;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\InspectionConverter\FileSystem\FileReader;
use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Inspection\ProblemFactory;
use Scheb\InspectionConverter\Inspection\ProblemIterator;
use Scheb\InspectionConverter\Test\TestCase;

class ProblemIteratorTest extends TestCase
{
    private const INSPECTION_FILE = 'InspectionFile.xml';
    private const PROJECT_ROOT = '/project/root';

    /**
     * @test
     */
    public function getAggregator_iterateXmlFile_yieldAllProblems(): void
    {
        $reader = $this->createReader();
        $problem = $this->createMock(Problem::class);

        $problem1 = "<problem><file>file1</file>\n</problem>";
        $problem2 = '<problem><file>file2</file></problem>';
        $problem3 = '<problem><file>file3</file></problem>';

        $problemFactory = $this->createMock(ProblemFactory::class);
        $problemFactory
            ->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                [self::PROJECT_ROOT, self::INSPECTION_FILE, new \SimpleXMLElement($problem1)],
                [self::PROJECT_ROOT, self::INSPECTION_FILE, new \SimpleXMLElement($problem2)],
                [self::PROJECT_ROOT, self::INSPECTION_FILE, new \SimpleXMLElement($problem3)]
            )
            ->willReturn($problem);

        $iterator = new ProblemIterator($reader, $problemFactory, self::PROJECT_ROOT);
        $result = iterator_to_array($iterator, false);

        $this->assertCount(3, $result);
    }

    /**
     * @return FileReader|MockObject
     */
    private function createReader(): MockObject
    {
        $xml = <<<XML
        <problems>

<problem><file>file1</file>
</problem>
        <problem><file>file2</file></problem>

<problem><file>file3</file></problem>

</problems>
XML;
        $chunks = str_split($xml, 5);
        $numChunks = count($chunks);
        $eofResponse = array_fill(0, $numChunks, false);
        $eofResponse[] = true; // EOF reached

        $reader = $this->createMock(FileReader::class);
        $reader
            ->expects($this->any())
            ->method('eof')
            ->willReturnOnConsecutiveCalls(...$eofResponse);
        $reader
            ->expects($this->exactly(count($chunks)))
            ->method('read')
            ->willReturnOnConsecutiveCalls(...$chunks);
        $reader
            ->expects($this->any())
            ->method('getFilePath')
            ->willReturn(self::INSPECTION_FILE);

        return $reader;
    }
}
