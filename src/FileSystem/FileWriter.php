<?php

namespace Scheb\InspectionConverter\FileSystem;

class FileWriter
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var bool
     */
    private $closed = false;

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->handle = @fopen($file, 'w');
        if (!$this->handle) {
            throw new FileSystemException('Cannot open file '.$file.' for write', FileSystemException::OPEN_FAILED);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function write(string $content): void
    {
        if (false === fwrite($this->handle, $content)) {
            throw new FileSystemException('Could not write '.strlen($content).' bytes to '.$this->file, FileSystemException::WRITE_FAILED);
        }
    }

    public function close(): void
    {
        if (!$this->closed) {
            fclose($this->handle);
            $this->closed = true;
        }
    }
}
