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
    private $projectRoot;

    public function __construct(string $inspectionsFile, ProblemFactory $problemFactory, string $projectRoot)
    {
        $this->inspectionsFile = $inspectionsFile;
        $this->problemFactory = $problemFactory;
        $this->projectRoot = $projectRoot;
    }

    public static function create(string $inspectionsFile, string $projectRoot): self
    {
        return new self($inspectionsFile, new ProblemFactory(), $projectRoot);
    }

    public function getIterator(): \Traversable
    {
        $problemXmlIterator = new ProblemXmlIterator(new FileReader($this->inspectionsFile));
        foreach ($problemXmlIterator as $problemXml) {
            $xmlElement = new \SimpleXMLElement($problemXml);
            yield $this->problemFactory->create($this->projectRoot, $this->inspectionsFile, $xmlElement);
        }
    }
}
