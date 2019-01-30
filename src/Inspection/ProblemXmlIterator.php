<?php

namespace Scheb\InspectionConverter\Inspection;

use Scheb\InspectionConverter\FileSystem\FileReader;

class ProblemXmlIterator implements \IteratorAggregate
{
    private const CHUNK_BYTES = 1024 * 10;
    private const XML_START = '<problem>';
    private const XML_END = '</problem>';
    private const XML_END_LENGTH = 10;

    /**
     * @var FileReader
     */
    private $fileReader;

    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function getIterator(): \Traversable
    {
        $chunk = '';
        while(!$this->fileReader->eof()) {
            $chunk .= $this->fileReader->read(self::CHUNK_BYTES);
            while (($startPos = strpos($chunk, self::XML_START)) !== false) {
                $endPos = strpos($chunk, self::XML_END);
                if ($endPos === false || $endPos < $startPos) {
                    break; // End element not found, read another chunk
                }

                $problem = substr($chunk, $startPos, $endPos + self::XML_END_LENGTH - $startPos);
                $chunk = substr($chunk, $endPos + self::XML_END_LENGTH);
                yield $problem;
            }
        }
    }
}
