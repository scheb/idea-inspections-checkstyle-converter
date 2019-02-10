<?php

namespace Scheb\Inspection\Converter\Checkstyle;

use Scheb\Inspection\Core\Inspection\Problem;

class CheckstyleGenerator
{
    /**
     * @var SeverityMapping
     */
    private $severityMapping;

    public function __construct(SeverityMapping $severityMapping)
    {
        $this->severityMapping = $severityMapping;
    }

    public function getHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<checkstyle version="4.3">';
    }

    public function getFooter(): string
    {
        return '</checkstyle>';
    }

    /**
     * @param string    $fileName
     * @param Problem[] $problems
     *
     * @return string
     */
    public function generateFileXmlElement(string $fileName, array $problems): string
    {
        $xml = sprintf('  <file name="%s">'."\n", htmlspecialchars($fileName));
        foreach ($problems as $problem) {
            $xml .= sprintf(
                '    <error line="%s" column="0" severity="%s" message="%s" source="%s"/>'."\n",
                $problem->getLine(),
                htmlspecialchars($this->severityMapping->mapSeverity($problem->getSeverity())),
                htmlspecialchars($problem->getDescription()),
                htmlspecialchars($this->sanitizeClass($problem))
            );
        }
        $xml .= '  </file>';

        return $xml;
    }

    private function sanitizeClass(Problem $problem): string
    {
        // Jenkins Next Generation Warnings plugin uses . to split the class name
        return str_replace('.', ';', rtrim($problem->getClass(), '.'));
    }
}
