<?php

namespace Scheb\InspectionConverter\Test\Checkstyle;

use Scheb\InspectionConverter\Checkstyle\CheckstyleGenerator;
use Scheb\InspectionConverter\Inspection\Problem;
use Scheb\InspectionConverter\Test\TestCase;

class CheckstyleGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function generateFileXmlElement_problemsGiven_returnXml(): void
    {
        $problems = [
            new Problem('InspectionName1', 'file.txt', 1, 'InspectionClass1', 'ERROR', 'Error description'),
            new Problem('InspectionName2', 'file.txt', 2, 'InspectionClass2', 'WARNING', 'Warning description'),
        ];

        $generator = new CheckstyleGenerator();
        $xml = $generator->generateFileXmlElement('file.txt', $problems);

        $expectedXml = <<<XML
  <file name="file.txt">
    <error line="1" severity="error" message="Error description" source="InspectionName1.InspectionClass1"/>
    <error line="2" severity="warning" message="Warning description" source="InspectionName2.InspectionClass2"/>
  </file>
XML;

        $this->assertEquals($expectedXml, $xml);
    }
}
