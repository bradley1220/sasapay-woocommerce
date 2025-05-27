<?php

include_once "Transport/SasaPayTransport.php";

class SasaPay
{
    protected $transaction;
    protected mixed $MerchantCode;
    protected mixed $NetworkCode;
    protected mixed $Currency;
    protected mixed $CallBackURL;

    public function __construct()
    {
        $this->MerchantCode = (int) get_option('merchant_code');
        $this->NetworkCode = get_option('network_code');
        $this->Currency = get_option('currency_code');
        $this->CallBackURL = get_option('callback_url');
    }

    /**
     * Execute request to mpesa api
     * @param string $endpoint
     * @param array $payload
     * @param string $method
     * @return array
     */
    public function request(string $endpoint, array $payload, string $method = 'post'): array
    {
        $transport = new SasaPayTransport();
        return $transport->request($endpoint, $payload, $method);
    }

    /**
     * Get Mpesa transaction timestamp YYYYMMDDHHMMSS
     * @return string
     */
    public function getTimeStamp(): string
    {
        $Timestamp = strtotime(time());
        $Timestamp = str_replace(':' , '', $Timestamp);
        $Timestamp = str_replace(' ' , '', $Timestamp);
        return str_replace('-' , '', $Timestamp);
    }

    /**
     * Sanitize Phone Number to SasaPay format
     * @param string $number
     * @return string
     */
    public function getMsisdn(string $number): string
    {
        $number = trim($number);
        $number = str_replace(' ', '', $number);
        $number = str_replace('+', '', $number);

        if (str_starts_with($number, '254')){
            if (str_starts_with($number, '2540'))
                $number = substr_replace($number, '', 3, 1);
            return $number;
        }
        $number = $number[0] === '0' ? ltrim($number, 0) : $number;
        return '254'.$number;
    }

}
