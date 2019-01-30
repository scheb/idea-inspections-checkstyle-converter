<?php

namespace Scheb\InspectionConverter\Test\FileSystem;

use Scheb\InspectionConverter\FileSystem\FileSystemException;
use Scheb\InspectionConverter\FileSystem\FileWriter;
use Scheb\InspectionConverter\Test\TestCase;

class FileWriterTest extends TestCase
{
    private const FILE = __DIR__.'/test.txt';
    private const INVALID_FILE = __DIR__.'/invalid/file.txt';

    protected function tearDown()
    {
        if (file_exists(self::FILE)) {
            unlink(self::FILE);
        }
    }

    /**
     * @test
     */
    public function construct_openInvalidFile_throwFileSystemException(): void
    {
        $this->expectException(FileSystemException::class);
        $this->expectExceptionCode(FileSystemException::OPEN_FAILED);

        new FileWriter(self::INVALID_FILE);
    }

    /**
     * @test
     */
    public function construct_writeFile_fileExistsAndHasContent(): void
    {
        $writer = new FileWriter(self::FILE);
        $writer->write("line1\n");
        $writer->write("line2\n");
        $writer->close();

        $this->assertFileExists(self::FILE);
        $this->assertStringEqualsFile(self::FILE, "line1\nline2\n");
    }
}
