<?php

namespace Scheb\Inspection\Converter\Checkstyle;

class SeverityMapping
{
    /**
     * @var array
     */
    private $severityMap;

    /**
     * @var string
     */
    private $defaultSeverity;

    public function __construct(array $severityMap, ?string $defaultSeverity)
    {
        $this->severityMap = $severityMap;
        $this->defaultSeverity = $defaultSeverity;
    }

    public function mapSeverity(string $severity): string
    {
        if (isset($this->severityMap[$severity])) {
            return $this->severityMap[$severity];
        }
        if ($this->defaultSeverity) {
            return $this->defaultSeverity;
        }

        return $severity;
    }
}
