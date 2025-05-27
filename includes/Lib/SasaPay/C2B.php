<?php


include_once "SasaPay.php";

class C2B extends SasaPay
{
    /**
     * Register URLs
     * @return array
     */
    public function register(): array
    {
        $this->transaction = [
            'MerchantCode' => (string) get_option('merchant_code'),
            'ConfirmationURL' => get_option('callback_url'),
//            'ValidationURL' => get_option('validation_url')
        ];
        return $this->request('payments/register-ipn-url/', $this->transaction);
    }

    /**
     * @param int $amount
     * @param string $msisdn
     * @param string $accountReference
     * @param string $transDesc
     * @return array
     */
    public function pay(int $amount, string $msisdn, string $accountReference,string $transDesc): array
    {
        $this->transaction = [
            "MerchantCode" => (string) $this->MerchantCode,
            "NetworkCode" => (string) $this->NetworkCode,
            "PhoneNumber" =>  $this->getMsisdn($msisdn),
            "TransactionDesc" => $transDesc,
            "AccountReference" => $accountReference,
            "Currency" => (string) $this->Currency,
            "Amount"=>$amount,
            'CallBackURL'=>(string)$this->CallBackURL
        ];
        return $this->request('payments/request-payment/', $this->transaction);
    }

    /**
     * @param string $checkout_request_id
     * @param $verification_code
     * @return array
     */
    public function complete_payment_process(string $checkout_request_id, $verification_code): array
    {
        $this->transaction=[
            "CheckoutRequestID"=>$checkout_request_id,
            "MerchantCode"=> (string)$this->MerchantCode,
            "VerificationCode"=>(string) $verification_code
        ];
        return $this->request('payments/process-payment/', $this->transaction);
    }
}
