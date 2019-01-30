<?php

namespace Scheb\InspectionConverter\Inspection;

class ProblemFactory
{
    public function create(string $projectPath, string $xmlFilename, \SimpleXMLElement $problemXml): Problem
    {
        $projectPathEnd = substr($projectPath, -1);
        if ('/' === $projectPathEnd || '\\' === $projectPathEnd) {
            $projectPath = substr($projectPath, 0, -1);
        }

        $problem = new Problem(
            $this->getInspectionName($xmlFilename),
            $this->getFilename($projectPath, $problemXml),
            (int) $problemXml->line,
            (string) $problemXml->problem_class,
            (string) $problemXml->problem_class['severity'],
            (string) $problemXml->description
        );

        return $problem;
    }

    private function getInspectionName(string $xmlFilename): string
    {
        preg_match('#(\w*)\.xml$#', $xmlFilename, $match);

        return $match[1] ?? 'UNKNOWN';
    }

    private function getFilename(string $projectPath, \SimpleXMLElement $problemXml): string
    {
        return str_replace('file://$PROJECT_DIR$/', $projectPath.'/', (string) $problemXml->file);
    }
}
