<?php

namespace Scheb\InspectionConverter\Inspection;

class ProblemAggregator
{
    /**
     * @var ProblemIteratorFactory
     */
    private $problemIteratorFactory;

    /**
     * @var string[]
     */
    private $ignoreInspectionsRegex;

    /**
     * @var string[]
     */
    private $ignoreFilesRegex;

    /**
     * @var string[]
     */
    private $ignoreMessagesRegex;

    public function __construct(ProblemIteratorFactory $problemIteratorFactory, array $ignoreInspections, array $ignoreFiles, array $ignoreMessages)
    {
        $this->problemIteratorFactory = $problemIteratorFactory;
        $this->ignoreInspectionsRegex = array_map([$this, 'createRegex'], $ignoreInspections);
        $this->ignoreFilesRegex = array_map([$this, 'createRegex'], $ignoreFiles);
        $this->ignoreMessagesRegex = array_map([$this, 'createRegex'], $ignoreMessages);
    }

    public function readInspections(array $inspectionsFiles, string $projectRoot): ProblemSummary
    {
        $problemSummary = new ProblemSummary();
        foreach ($inspectionsFiles as $inspectionsFile) {
            if ($this->matchRegex($inspectionsFile, $this->ignoreInspectionsRegex)) {
                continue;
            }

            /** @var Problem $problem */
            $problems = $this->problemIteratorFactory->createProblemIterator($inspectionsFile, $projectRoot);
            foreach ($problems as $problem) {
                $fileName = $problem->getFileName();
                if ($this->matchRegex($fileName, $this->ignoreFilesRegex)) {
                    continue;
                }
                if ($this->matchRegex($problem->getDescription(), $this->ignoreMessagesRegex)) {
                    continue;
                }

                $problemSummary->addProblem($fileName, $problem);
            }
        }

        return $problemSummary;
    }

    private function createRegex(string $pattern): string
    {
        return '#'.str_replace('#', '\\#', $pattern).'#';
    }

    private function matchRegex(string $value, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}
