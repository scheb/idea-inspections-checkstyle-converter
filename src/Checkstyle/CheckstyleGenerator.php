<?php

namespace Scheb\InspectionConverter\Checkstyle;

class CheckstyleGenerator
{
    public function getHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<checkstyle version="1.0.0">';
    }

    public function getFooter(): string
    {
        return '</checkstyle>';
    }

    /**
     * @param string                                          $fileName
     * @param \Scheb\InspectionConverter\Inspection\Problem[] $problems
     *
     * @return string
     */
    public function generateFileXmlElement(string $fileName, array $problems): string
    {
        $xml = sprintf('<file name="%s">'."\n", htmlspecialchars($fileName));
        foreach ($problems as $problem) {
            $xml .= sprintf(
                '    <error line="%s" column="0" severity="%s" message="%s"/>'."\n",
                $problem->getLine(),
                htmlspecialchars(strtolower($problem->getSeverity())),
                htmlspecialchars($problem->getDescription())
            );
        }
        $xml .= '</file>';

        return $xml;
    }
}
