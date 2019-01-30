<?php

namespace Scheb\InspectionConverter\FileSystem;

abstract class FileHandle
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var bool
     */
    protected $closed = false;

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->handle = $this->createHandle($file);
        if (!$this->handle) {
            throw new FileSystemException('Cannot open file '.$file, FileSystemException::OPEN_FAILED);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    abstract protected function createHandle(string $file);

    public function getFilePath(): string
    {
        return $this->file;
    }

    public function close(): void
    {
        if (!$this->closed) {
            fclose($this->handle);
            $this->closed = true;
        }
    }
}
