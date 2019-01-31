<?php

namespace Scheb\InspectionConverter\Inspection;

class ProblemSummary
{
    /**
     * @var int
     */
    private $numFiles;

    /**
     * @var int
     */
    private $numProblems;

    /**
     * @var Problem[][]
     */
    private $problemsPerFile = [];

    public function addProblem(string $fileName, Problem $problem): void
    {
        if (!isset($this->problemsPerFile[$fileName])) {
            $this->problemsPerFile[$fileName] = [];
            ++$this->numFiles;
        }
        $this->problemsPerFile[$fileName][] = $problem;
        ++$this->numProblems;
    }

    public function getNumFiles(): int
    {
        return $this->numFiles;
    }

    public function getNumProblems(): int
    {
        return $this->numProblems;
    }

    /**
     * @return Problem[][]
     */
    public function getProblemsByFile(): array
    {
        return $this->problemsPerFile;
    }
}
