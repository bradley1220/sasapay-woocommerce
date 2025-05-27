<?php

/**
 * Library to handle payment processing
 */
class SasaPayTransport
{
    public mixed $token;

    public mixed $entrypoint;

    public function __construct()
    {
        $this->entrypoint = get_option('api_base_url');
        $this->token = $_SESSION['sasapay_token'] ?? $this->getAccessToken();
    }
    /**
     * @return string
     */
    protected function getAccessToken(): string
    {
        $key = get_option('client_id');
        $secret = get_option('client_secret');
        $credentials = base64_encode("$key:$secret");
        $url = $this->getUrl('auth/token/', ['grant_type' => 'client_credentials']);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials));
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $curl_response = curl_exec($curl);

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $body = substr($curl_response, $headerSize);

        curl_close($curl);

        $body = json_decode($body, true);
        $_SESSION['sasapay_token']=$body['access_token'];
        return $body['access_token'];
    }

    /**
     * @param $endpoint
     * @param array $data
     * @param string $method
     * @return array
     */
    public function request($endpoint, array $data = [], string $method = 'post'):array
    {
        try{
            $token = $this->getAccessToken();
            $url = $this->getUrl($endpoint);
            $header=[
                'Content-Type:application/json',
                'Authorization:Bearer '.$token
            ];

            $curl = curl_init();
            curl_setopt($curl, CURLINFO_HEADER_OUT , true);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

            $curl_post_data = $data;

            $data_string = json_encode($curl_post_data);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            if ($method=='post') {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            }

            $curl_response = curl_exec($curl);

            curl_close($curl);

            return json_decode($curl_response, true);
        } catch (Exception $exception){
            return (array) $this->error($exception->getMessage());
        }
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return string
     */
    public function getUrl(string $endpoint, array $data = []): string
    {
        $url = $this->entrypoint. ltrim($endpoint, '/');
        return $url . $this->appendData($data);
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function appendData(array $data = []): ?string
    {
        if (!count($data)){
            return null;
        }

        $data = array_map(function ($item){
            return is_null($item) ? '' : $item;
        }, $data);

        return '?' . http_build_query($data);
    }

    /**
     * @param string|null $message
     * @param int $code
     * @param null $data
     * @return string|false
     */
    protected function error(string $message = null, int $code = 500, $data = null): bool|string
    {
        return json_encode([
            'status' => 'Error',
            'message' => $message,
            'data' => $data
        ], $code);
    }

}
