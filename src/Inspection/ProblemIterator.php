<?php

namespace Scheb\InspectionConverter\Inspection;

use Scheb\InspectionConverter\FileSystem\FileReader;

class ProblemIterator implements \IteratorAggregate
{
    private const CHUNK_BYTES = 1024 * 10;
    private const XML_START = '<problem>';
    private const XML_END = '</problem>';
    private const XML_END_LENGTH = 10;

    /**
     * @var FileReader
     */
    private $fileReader;

    /**
     * @var ProblemFactory
     */
    private $problemFactory;

    /**
     * @var string
     */
    private $projectRoot;

    public function __construct(FileReader $fileReader, ProblemFactory $problemFactory, string $projectRoot)
    {
        $this->fileReader = $fileReader;
        $this->problemFactory = $problemFactory;
        $this->projectRoot = $projectRoot;
    }

    public function getIterator(): \Traversable
    {
        $inspectionFile = $this->fileReader->getFilePath();
        foreach ($this->iterateXml() as $problemXml) {
            yield $this->problemFactory->create($this->projectRoot, $inspectionFile, new \SimpleXMLElement($problemXml));
        }
    }

    private function iterateXml(): \Traversable
    {
        $chunk = '';
        while (!$this->fileReader->eof()) {
            $chunk .= $this->fileReader->read(self::CHUNK_BYTES);
            while (false !== ($startPos = strpos($chunk, self::XML_START))) {
                $endPos = strpos($chunk, self::XML_END);
                if (false === $endPos || $endPos < $startPos) {
                    break; // End element not found, read another chunk
                }

                $problem = substr($chunk, $startPos, $endPos + self::XML_END_LENGTH - $startPos);
                $chunk = substr($chunk, $endPos + self::XML_END_LENGTH);
                yield $problem;
            }
        }
    }
}
