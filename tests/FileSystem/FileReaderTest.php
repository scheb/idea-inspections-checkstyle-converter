<?php

namespace Scheb\InspectionConverter\Test\FileSystem;

use Scheb\InspectionConverter\FileSystem\FileSystemException;
use Scheb\InspectionConverter\FileSystem\FileReader;
use Scheb\InspectionConverter\Test\TestCase;

class FileReaderTest extends TestCase
{
    private const FILE = __DIR__.'/_fixtures/test.txt';
    private const INVALID_FILE = __DIR__.'/invalid/file.txt';

    /**
     * @test
     */
    public function construct_openInvalidFile_throwFileSystemException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->expectExceptionCode(FileSystemException::OPEN_FAILED);

        new FileReader(self::INVALID_FILE);
    }

    /**
     * @test
     */
    public function construct_readFile_returnBytesAndEof(): void
    {
        $reader = new FileReader(self::FILE);
        $this->assertEquals('1234', $reader->read(4));
        $this->assertFalse($reader->eof(), 'EOF must not be reached');

        $this->assertEquals('5678', $reader->read(4));
        $this->assertFalse($reader->eof(), 'EOF must not be reached');

        $reader->read(4); // EOF reached
        $this->assertTrue($reader->eof(), 'EOF must be reached');
    }
}
