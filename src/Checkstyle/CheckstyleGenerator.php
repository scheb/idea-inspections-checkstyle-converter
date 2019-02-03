<?php

namespace Scheb\InspectionConverter\Checkstyle;

use Scheb\InspectionConverter\Inspection\Problem;

class CheckstyleGenerator
{
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
                '    <error line="%s" severity="%s" message="%s" source="%s"/>'."\n",
                $problem->getLine(),
                htmlspecialchars(strtolower($problem->getSeverity())),
                htmlspecialchars($problem->getDescription()),
                htmlspecialchars($problem->getClass())
            );
        }
        $xml .= '  </file>';

        return $xml;
    }
}
