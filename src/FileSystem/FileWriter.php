<?php

namespace Scheb\InspectionConverter\FileSystem;

class FileWriter extends FileHandle
{
    protected function createHandle(string $file)
    {
        return @fopen($file, 'w');
    }

    public function write(string $content): void
    {
        if (false === fwrite($this->handle, $content)) {
            throw new FileSystemException('Could not write '.strlen($content).' bytes to '.$this->file, FileSystemException::WRITE_FAILED);
        }
    }
}
