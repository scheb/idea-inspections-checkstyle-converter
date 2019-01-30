<?php

namespace Scheb\InspectionConverter\FileSystem;

class FileSystemException extends \Exception
{
    public const OPEN_FAILED = 1;
    public const WRITE_FAILED = 2;
}
