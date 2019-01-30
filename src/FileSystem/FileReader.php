<?php

namespace Scheb\InspectionConverter\FileSystem;

class FileReader extends FileHandle
{
    protected function createHandle(string $file)
    {
        return @fopen($file, 'r');
    }

    public function read(int $bytes): string
    {
        return fread($this->handle, $bytes);
    }

    public function eof(): bool
    {
        return feof($this->handle);
    }
}
