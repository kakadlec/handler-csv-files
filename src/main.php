<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\CsvHandler\CsvProcessor;
use App\CsvHandler\Internal\CsvReader;
use App\CsvHandler\Internal\CsvWriter;

$inputFile = __DIR__ . '/../export/all_2024-10-30_13-36-36.csv';
$affectedPayments =  __DIR__ . '/../result/affected_payments.csv';
$badTokens =  __DIR__ . '/../result/bad_tokens.csv';

try {
    $reader = new CsvReader($inputFile);
    $affectedPaymentsWriter = new CsvWriter($affectedPayments);
    $badTokensWriter = new CsvWriter($badTokens);

    $processor = new CsvProcessor($reader, $affectedPaymentsWriter, $badTokensWriter);
    $processor->process();

    echo "CSV processado com sucesso!\n";

    $reader->close();
    $affectedPaymentsWriter->close();
    $badTokensWriter->close();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
