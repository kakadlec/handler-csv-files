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
        [$request_date, $payment_id, , $token_request, $token_response, $card_bin_request, $card_bin_response] = $row;
        $this->logProgress($request_date);

        if ($this->isMismatch($token_request, $token_response, $card_bin_request, $card_bin_response)) {
            if (!array_key_exists($token_response, $this->setOfTokensWithDpan)) {
                $this->setOfTokensWithDpan[$token_response] = true;
                $this->tokensWriter->writeRow([$token_response, $payment_id, $request_date]);
            }
        } elseif ($this->isDpanTokenRequestMatch($token_request, $payment_id)) {
                $this->paymentsWriter->writeRow([$payment_id]);
        }
    }

    private function logProgress(string $request_date): void
    {
        if (random_int(1, 10000) === 42) {
            echo "Processed until: $request_date\n";
        }
    }

    private function isMismatch($token_request, $token_response, $card_bin_request, $card_bin_response): bool
    {
        $token_mismatch = !empty($token_request) && !empty($token_response) && $token_request !== $token_response;
        $card_bin_mismatch = !empty($card_bin_request)
            && !empty($card_bin_response)
            && $card_bin_request !== '******'
            && $card_bin_request !== $card_bin_response;
        return $token_mismatch || $card_bin_mismatch;
    }

    private function isDpanTokenRequestMatch($token_request, $payment_id): bool
    {
        return isset($this->setOfTokensWithDpan[$token_request]) && !empty($payment_id);
    }
}
