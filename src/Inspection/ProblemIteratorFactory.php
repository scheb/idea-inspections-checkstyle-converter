<?php

namespace Scheb\InspectionConverter\Inspection;

use Scheb\InspectionConverter\FileSystem\FileReader;

class ProblemIteratorFactory
{
    public function createProblemIterator(string $inspectionsFile, string $projectRoot): ProblemIterator
    {
        return new ProblemIterator(new FileReader($inspectionsFile), new ProblemFactory(), $projectRoot);
    }
}
