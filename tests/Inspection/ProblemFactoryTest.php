<?php

namespace Scheb\InspectionConverter\Test\Inspection;

use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Inspection\ProblemFactory;
use Scheb\InspectionConverter\Test\TestCase;

class ProblemFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function create_xmlGiven_returnProblem(): void
    {
        $xml = file_get_contents(__DIR__.'/_fixtures/problem.xml');
        $xmlElement = new \SimpleXMLElement($xml);
        $factory = new ProblemFactory();
        $createdProblem = $factory->create('/path/to/project/', '/inspections/InspectionName.xml', $xmlElement);
        $expectedProblem = new Problem('InspectionName', '/path/to/project/src/file', 123, 'Problem class', 'WARNING', 'Description');

        $this->assertEquals($expectedProblem, $createdProblem);
    }
}
