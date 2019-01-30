<?php

namespace Scheb\InspectionConverter\Inspection;

use Scheb\InspectionConverter\FileSystem\FileReader;

class ProblemIterator implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $inspectionsFile;

    /**
     * @var ProblemFactory
     */
    private $problemFactory;

    /**
     * @var string
     */
    private $projectPath;

    public function __construct(string $inspectionsFile, ProblemFactory $problemFactory, string $projectPath)
    {
        $this->inspectionsFile = $inspectionsFile;
        $this->problemFactory = $problemFactory;
        $this->projectPath = $projectPath;
    }

    public static function create(string $inspectionsFile, string $projectPath): self
    {
        return new self($inspectionsFile, new ProblemFactory(), $projectPath);
    }

    public function getIterator(): \Traversable
    {
        $problemXmlIterator = new ProblemXmlIterator(new FileReader($this->inspectionsFile));
        foreach ($problemXmlIterator as $problemXml) {
            $xmlElement = new \SimpleXMLElement($problemXml);
            yield $this->problemFactory->create($this->projectPath, $this->inspectionsFile, $xmlElement);
        }
    }
}
