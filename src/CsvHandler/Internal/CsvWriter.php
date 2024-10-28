<?php

declare(strict_types=1);

namespace App\CsvHandler\Internal;

class CsvWriter
{
    private $fileHandle;

    public function __construct($filePath)
    {
        $this->fileHandle = fopen($filePath, 'w');
        if ($this->fileHandle === false) {
            throw new \Exception("Could not open the file: $filePath");
        }
    }

    public function writeRow(array $row): void
    {
        fputcsv($this->fileHandle, $row, ',', "'");
    }

    public function close(): void
    {
        fclose($this->fileHandle);
    }
}
