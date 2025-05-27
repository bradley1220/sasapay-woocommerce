<?php

namespace App\Lib\SasaPay;

class MerchantAccount extends SasaPay
{
    /**
     * @return array
     */
    public function account_balance(): array
    {
        return $this->request("payments/check-balance/?MerchantCode=$this->MerchantCode",[],'get');
    }

    /**
     * @param string $CheckoutRequestId
     * @return array
     */
    public function check_transaction_status(string $CheckoutRequestId): array
    {
        $this->transaction=[
            "MerchantCode"=>$this->MerchantCode,
            "CheckoutRequestId"=> $CheckoutRequestId
        ];
        return $this->request("transactions/status/",$this->transaction);
    }
    /**
     * @param string $CheckoutRequestId
     * @return array
     */
    public function verify_transaction(string $TransactionCode): array
    {
        $this->transaction=[
            "MerchantCode"=>$this->MerchantCode,
            "TransactionCode"=> $TransactionCode
        ];
        return $this->request("transactions/verify/",$this->transaction);
    }
}
