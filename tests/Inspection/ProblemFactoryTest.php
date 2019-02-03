<?php

namespace Scheb\InspectionConverter\Test\Inspection;

use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Inspection\ProblemFactory;
use Scheb\InspectionConverter\Test\TestCase;

class ProblemFactoryTest extends TestCase
{
    /**
     * @var ProblemFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new ProblemFactory();
    }

    /**
     * @test
     */
    public function create_xmlGiven_returnProblem(): void
    {
        $xmlElement = $this->loadXmlElement();
        $createdProblem = $this->factory->create('/path/to/project/', '/inspections/InspectionName.xml', $xmlElement);

        $expectedProblem = new Problem('InspectionName', '/path/to/project/src/file', 123, 'Problem class', 'WARNING', 'Description >');
        $this->assertEquals($expectedProblem, $createdProblem);
    }

    /**
     * @test
     */
    public function create_noProjectRoot_useRelativePath(): void
    {
        $xmlElement = $this->loadXmlElement();
        $createdProblem = $this->factory->create('', '/inspections/InspectionName.xml', $xmlElement);

        $expectedProblem = new Problem('InspectionName', 'src/file', 123, 'Problem class', 'WARNING', 'Description >');
        $this->assertEquals($expectedProblem, $createdProblem);
    }

    private function loadXmlElement(): \SimpleXMLElement
    {
        $xml = file_get_contents(__DIR__.'/_fixtures/problem.xml');

        return new \SimpleXMLElement($xml);
    }
}
