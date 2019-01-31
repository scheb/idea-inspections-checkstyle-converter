<?php

namespace Scheb\InspectionConverter\Inspection;

class Problem
{
    /**
     * @var string
     */
    private $inspectionName;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $severity;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $inspectionName, string $filename, int $line, string $class, string $severity, string $description)
    {
        $this->inspectionName = $inspectionName;
        $this->filename = $filename;
        $this->line = $line;
        $this->class = $class;
        $this->severity = $severity;
        $this->description = $description;
    }

    public function getInspectionName(): string
    {
        return $this->inspectionName;
    }

    public function getFileName(): string
    {
        return $this->filename;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
