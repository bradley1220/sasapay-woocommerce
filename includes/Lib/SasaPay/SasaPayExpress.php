<?php

namespace App\Lib\SasaPay;

class SasaPayExpress extends SasaPay
{
    protected $Timestamp;
    protected $BusinessShortCode;
    protected $PassKey;
    protected $Password;

    /**
     * MpesaExpress constructor.
     */
    public function __construct()
    {
        $this->Timestamp = $this->getTimeStamp();
        $this->BusinessShortCode = config('opicash.providers.sasapay.onlineShortCode');
        $this->PassKey = config('opicash.providers.sasapay.passkey');
        $this->Password = base64_encode($this->BusinessShortCode.$this->PassKey.$this->Timestamp);
    }

    /**
     * @param int $Amount
     * @param $PartyA
     * @param $AccountReference
     * @param $TransactionDesc
     * @return array
     */
    public function pay(int $Amount, $PartyA, $AccountReference, $TransactionDesc): array
    {
        $transaction = [
            'BusinessShortCode' => $this->BusinessShortCode,
            'Password' => $this->Password,
            'Timestamp' => $this->Timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $Amount,
            'PartyA' => $this->getMsisdn($PartyA),
            'PartyB' => $this->BusinessShortCode,
            'PhoneNumber' => $this->getMsisdn($PartyA),
            'CallBackURL' =>config('opicash.providers.mpesa.stk.callBackUrl'),
            'AccountReference' => substr($AccountReference, -4),
            'TransactionDesc' => $TransactionDesc
        ];

        return $this->request('mpesa/stkpush/v1/processrequest', $transaction);
    }

    /**
     * @param $CheckoutRequestID
     * @return array
     */
    public function status($CheckoutRequestID): array
    {
        $transaction = [
            'BusinessShortCode' => $this->BusinessShortCode,
            'Password' => $this->Password,
            'Timestamp' => $this->Timestamp,
            'CheckoutRequestID' => $CheckoutRequestID
        ];

        return $this->request('mpesa/stkpushquery/v1/query', $transaction);
    }

}
