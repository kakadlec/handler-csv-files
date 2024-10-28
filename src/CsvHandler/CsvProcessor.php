<?php

declare(strict_types=1);

namespace App\CsvHandler;

use App\CsvHandler\Internal\CsvReader;
use App\CsvHandler\Internal\CsvWriter;

class CsvProcessor
{
    private CsvReader $reader;
    private CsvWriter $paymentsWriter;
    private CsvWriter $tokensWriter;
    private array $setOfTokensWithDpan = [];

    public function __construct(CsvReader $reader, CsvWriter $payments, CsvWriter $tokens)
    {
        $this->reader = $reader;
        $this->paymentsWriter = $payments;
        $this->tokensWriter = $tokens;
    }

    public function process(): void
    {
        while ($row = $this->reader->getNextRow()) {
            $this->processRow($row);
        }
    }

    private function processRow($row): void
    {
        $request_date = $row[0];
        $payment_id = $row[1];
        $token_request = $row[3];
        $token_response = $row[4];
        $card_bin_request = $row[5];
        $card_bin_response = $row[6];

        if (random_int(1,10000) == 42) {
            echo "processed until: " . $request_date . "\n";
        }

        if ((!empty($token_request) && !empty($token_response) && $token_request !== $token_response)
            || (!empty($card_bin_request) && !empty($card_bin_response) && $card_bin_request !== $card_bin_response)
        ) {
            $this->setOfTokensWithDpan[$token_response] = true;
            $this->tokensWriter->writeRow([$token_response]);
        } else if (isset($this->setOfTokensWithDpan[$token_request])) {
           if (!empty($payment_id)) {
               $this->paymentsWriter->writeRow([$payment_id]);
           }
        }
    }
}
