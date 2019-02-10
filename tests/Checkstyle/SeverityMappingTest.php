<?php

namespace Scheb\Inspection\Converter\Test\Checkstyle;

use Scheb\Inspection\Converter\Checkstyle\SeverityMapping;
use Scheb\Inspection\Converter\Test\TestCase;

class SeverityMappingTest extends TestCase
{
    /**
     * @test
     */
    public function mapSeverity_noMappingFound_returnDefault(): void
    {
        $mapping = new SeverityMapping(['ERROR' => 'error'], 'default');
        $returnValue = $mapping->mapSeverity('UNKNOWN');
        $this->assertEquals('default', $returnValue);
    }

    /**
     * @test
     */
    public function mapSeverity_mappingFound_returnMappedValue(): void
    {
        $mapping = new SeverityMapping(['ERROR' => 'error'], 'default');
        $returnValue = $mapping->mapSeverity('ERROR');
        $this->assertEquals('error', $returnValue);
    }

    /**
     * @test
     */
    public function mapSeverity_noMappingAndNoDefault_returnSameValue(): void
    {
        $mapping = new SeverityMapping(['ERROR', 'error'], null);
        $returnValue = $mapping->mapSeverity('UNKNOWN');
        $this->assertEquals('UNKNOWN', $returnValue);
    }
}
