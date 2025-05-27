<?php

namespace App\Lib\SasaPay;
use Illuminate\Support\Str;

class B2C extends SasaPay
{
    /**
     * @param int $Amount
     * @param string $PartyB
     * @param string $Channel
     * @param $Reason
     * @return array
     */
    public function pay(int $Amount, string $PartyB, string $Channel, $Reason): array
    {
        $this->transaction = [
            "MerchantCode" => $this->MerchantCode,
            "MerchantTransactionReference" => Str::random(10),
            "Amount" => $Amount,
            "Currency" => $this->Currency,
            "ReceiverNumber" => $this->getMsisdn($PartyB),
            "Channel" => $Channel,
            "Reason" => $Reason,
            "CallBackURL" => config('opicash.providers.sasapay.b2c.confirmation_url'),
        ];
        return $this->request('payments/b2c/', $this->transaction);
    }
}
