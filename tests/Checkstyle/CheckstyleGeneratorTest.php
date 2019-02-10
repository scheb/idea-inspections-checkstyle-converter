<?php

namespace Scheb\Inspection\Converter\Test\Checkstyle;

use Scheb\Inspection\Converter\Checkstyle\CheckstyleGenerator;
use Scheb\Inspection\Converter\Test\TestCase;
use Scheb\Inspection\Core\Inspection\Problem;

class CheckstyleGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function generateFileXmlElement_problemsGiven_returnXml(): void
    {
        $problems = [
            new Problem('InspectionName1', 'file.txt', 1, 'Inspection.Class1.', 'ERROR', 'Error description'),
            new Problem('InspectionName2', 'file.txt', 2, 'Inspection.Class2.', 'WARNING', 'Warning description'),
        ];

        $generator = new CheckstyleGenerator();
        $xml = $generator->generateFileXmlElement('file.txt', $problems);

        $expectedXml = <<<XML
  <file name="file.txt">
    <error line="1" severity="error" message="Error description" source="Inspection;Class1"/>
    <error line="2" severity="warning" message="Warning description" source="Inspection;Class2"/>
  </file>
XML;

        $this->assertEquals($expectedXml, $xml);
    }
}
