<?php


class UnusedCSS_Api
{

    public $apiUrl = 'https://api.freshpixl.com/unusedcss';
    public $client;

    /**
     * UnusedCSS_Api constructor.
     */
    public function __construct()
    {
        $this->client = new GuzzleHttp\Client();
    }

    public function get($url)
    {
        try {
            $response = $this->client->get($this->apiUrl . '?url=' . $url . '?doing_unused_fetch=true');

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody()->getContents());
            }

            return null;
        }
        catch(\GuzzleHttp\Exception\ServerException $e) {
            error_log($e->getMessage());
            return null;
        }

    }


}