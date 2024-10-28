<?php

declare(strict_types=1);

namespace App\CsvHandler\Internal;

class CsvReader
{
    private $fileHandle;

    public function __construct($filePath)
    {
        $this->fileHandle = fopen($filePath, 'r');
        if ($this->fileHandle === false) {
            throw new \Exception("Could not open the file: $filePath");
        }
    }

    public function getNextRow()
    {
        return fgetcsv($this->fileHandle);
    }

    public function close()
    {
        fclose($this->fileHandle);
    }
}
