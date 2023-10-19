<?php

namespace Beautyalist;

/**
 * Class API.
 */
class API
{
    const VERSION = '0.0.1';
    const URL = 'https://api.licensify.io';
    protected $client_id;
    protected $client_secret;

    /**
     * API constructor.
     *
     * @param string $client_id     API access - client_id
     * @param string $client_secret API access - client_secret
     */
    public function __construct($client_id, $client_secret)
    {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
    }

    /**
     * Get user information from access_token.
     *
     * @param string $access_token Access token
     *
     * @return array|mixed
     */
    public function getUserData($access_token)
    {
        $data = [];
        $url  = '/openid/v1.1/info';
        $curl = new Client\CurlClient();

        $headers = ['Content-type:application/json'];
        $params  = ['access_token' => $access_token];
        $result  = $curl->startRequest('post', self::URL.$url, $headers, $params);
        if ($result['code'] && 200 == $result['code']) {
            if (!empty($result['body'])) {
                $data = \json_decode($result['body'], true);
            }
        } else {
            error_log('Beautyalist\API: Request error');
            error_log($result['body']);
        }

        return $data;
    }

    /**
     * Replace one-time code to permanent.
     *
     * @param string $code One-time code
     *
     * @return array|mixed
     */
    public function getToken($code)
    {
        $data = [];
        $url  = '/openid/v1.1/token';
        $curl = new Client\CurlClient();

        $headers = ['Content-type:application/json'];
        $params  = [
            'code'          => $code,
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
        ];

        $result = $curl->startRequest('post', self::URL.$url, $headers, $params);
        if ($result['code'] && 200 == $result['code']) {
            if (!empty($result['body'])) {
                $data = \json_decode($result['body'], true);
            }
        } else {
            error_log('Beautyalist\API: Request error');
            error_log($result['body']);
        }

        return $data;
    }

    /**
     * Get API status.
     *
     * @return array|mixed
     */
    public function getStatus()
    {
        $data = [];
        $url  = '/status';
        $curl = new Client\CurlClient();

        $headers = [];
        $params  = [];

        $result = $curl->startRequest('get', self::URL.$url, $headers, $params);
        if ($result['code'] && 200 == $result['code']) {
            if (!empty($result['body'])) {
                $data = \json_decode($result['body'], true);
            }
        } else {
            error_log('Beautyalist\API: Request error');
            error_log($result['body']);
        }

        return $data;
    }

    /**
     * Get API version.
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }
}
