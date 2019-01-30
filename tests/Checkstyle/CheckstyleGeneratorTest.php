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
            new Problem('Inspection Name 1', 'file.txt', 1, 'Inspection Class 1', 'ERROR', 'Error description'),
            new Problem('Inspection Name 2', 'file.txt', 2, 'Inspection Class 2', 'WARNING', 'Warning description'),
        ];

        $generator = new CheckstyleGenerator();
        $xml = $generator->generateFileXmlElement('file.txt', $problems);

        $expectedXml = <<<XML
<file name="file.txt">
    <error line="1" column="0" severity="error" message="Error description"/>
    <error line="2" column="0" severity="warning" message="Warning description"/>
</file>
XML;

        $this->assertEquals($expectedXml, $xml);
    }
}
