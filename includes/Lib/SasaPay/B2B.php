<?php

namespace App\Lib\SasaPay;

use Illuminate\Support\Str;

class B2B extends SasaPay
{
    /**
     * @param int $Amount
     * @param string $PartyB
     * @param string $Channel
     * @param $Reason
     * @return array
     */
    public function pay(int $Amount, string $PartyB, $Reason): array
    {
        $this->transaction = [
            "MerchantCode" => $this->MerchantCode,
            "MerchantTransactionReference" => Str::random(10),
            "Amount" => $Amount,
            "Currency" => $this->Currency,
            "ReceiverMerchantCode" => $PartyB,
            "Reason" => $Reason,
            "CallBackURL" => config('opicash.providers.sasapay.b2b.confirmation_url'),
        ];
        return $this->request('payments/b2b/', $this->transaction);
    }
}
