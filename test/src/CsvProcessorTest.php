<?php

declare(strict_types=1);

namespace Tests;

use App\CsvHandler\CsvProcessor;
use App\CsvHandler\Internal\CsvReader;
use App\CsvHandler\Internal\CsvWriter;
use PHPUnit\Framework\TestCase;

class CsvProcessorTest extends TestCase
{
    public function testProcessCSV(): void
    {
        $input = $this->createInputFile();
        $affectedPayments = $this->createOutputFile();
        $badTokens = $this->createOutputFile();

        $reader = $this->createReader($input);
        $affectedPaymentsWriter = $this->createWriter($affectedPayments);
        $badTokensWriter = $this->createWriter($badTokens);

        $processor = new CsvProcessor($reader, $affectedPaymentsWriter, $badTokensWriter);

        $processor->process();

        rewind($affectedPayments);
        rewind($badTokens);
        $outputContent = stream_get_contents($affectedPayments);
        $expected = "132501037557\n";

        $outputBadContent = stream_get_contents($badTokens);
        $bad_tokens_expected = "token3,132501037557,'2024-07-11 00:00:02'\n";

        $this->assertEquals($expected, $outputContent);
        $this->assertEquals($bad_tokens_expected, $outputBadContent);


        fclose($input);
        fclose($affectedPayments);
        fclose($badTokens);
    }

    private function createInputFile()
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, "2024-07-11 00:00:01,132501037555,6251,token1,token1,,491566\n");
        fwrite($input, "2024-07-11 00:00:02,132501037557,4690,token2,token3,557908,557908\n");
        fwrite($input, "2024-07-11 00:00:02,132501037557,4690,token2,token3,557908,557908\n");
        fwrite($input, "2024-07-11 00:00:02,132501037557,4690,token3,,557908,557908\n");
        fwrite($input, "2024-07-11 00:00:02,132501037557,4690,,token4,******,550210\n");
        rewind($input);

        return $input;
    }

    private function createOutputFile()
    {
        return fopen('php://memory', 'r+');
    }

    private function createReader($inputStream): CsvReader
    {
        return new class($inputStream) extends CsvReader {
            private $stream;

            public function __construct($stream)
            {
                $this->stream = $stream;
            }

            public function getNextRow(): false|array
            {
                return fgetcsv($this->stream);
            }
        };
    }

    private function createWriter($outputStream): CsvWriter
    {
        return new class($outputStream) extends CsvWriter {
            private $stream;

            public function __construct($stream)
            {
                $this->stream = $stream;
            }

            public function writeRow(array $row): void
            {
                fputcsv($this->stream, $row, ',', "'");
            }
        };
    }
}
