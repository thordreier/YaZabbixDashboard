<?php
namespace YaZabbixDashboard;

class ZabbixApi {
    private $url;
    private $token;

    public function __construct($url, $token) {
        $this->url = $url;
        $this->token = $token;
    }

    public function request($method, $params=[]) {
        $curl = curl_init();
        $curlparams = [
            // CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$this->token,
                'Content-type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'jsonrpc' => '2.0',
                'method' => $method,
                'params' => $params,
                'id' => number_format(microtime(true), 4, '', ''),
            ]),
        ];
        curl_setopt_array($curl, $curlparams);
        $response_string = curl_exec($curl);
        $response = json_decode($response_string, 1);
        if (isset($response['error']))
        {
            return 'ERROR FIX ERROR FIX ERROR FIX ERROR FIXXX';
        }
        return $response['result'];
    }
}
