<?php

namespace Beautyalist\Client;

use Beautyalist\Util;

/**
 * Class CurlClient.
 */
class CurlClient
{
    const TIMEOUT = 60;
    const CONNECTION_TIMEOUT = 30;
    protected static $instance;
    protected $curl;

    /**
     * Client Singleton.
     */
    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Init new curl.
     */
    private function init()
    {
        $this->close();
        $this->curl = \curl_init();
    }

    /**
     * Closes the curl.
     */
    private function close()
    {
        if (null !== $this->curl) {
            \curl_close($this->curl);
            $this->curl = null;
        }
    }

    /**
     * Resets the curl.
     */
    private function reset()
    {
        if (null !== $this->curl) {
            \curl_reset($this->curl);
        } else {
            $this->init();
        }
    }

    public function startRequest($requestMethod, $url, $headers, $params)
    {
        $options = $this->constructRequest($requestMethod, $url, $headers, $params);

        return $this->execute($options['options'], $options['url']);
    }

    private function constructRequest($requestMethod, $url, $headers, $params)
    {
        $options = [];

        $requestMethod = \strtolower($requestMethod);
        switch ($requestMethod) {
            case 'get':
                $options[\CURLOPT_HTTPGET] = 1;
                if (\count($params) > 0) {
                    $url .= '?'.Util\Util::encodeParameters($params);
                }
                break;

            case 'post':
                $options[\CURLOPT_POST]       = 1;
                $options[\CURLOPT_POSTFIELDS] = \json_encode($params);
                break;

            default:
                throw new \Exception('Didnt recognize request method');
        }

        $options[\CURLOPT_URL]            = Util\Util::utf8($url);
        $options[\CURLOPT_RETURNTRANSFER] = true;
        $options[\CURLOPT_CONNECTTIMEOUT] = self::CONNECTION_TIMEOUT;
        $options[\CURLOPT_TIMEOUT]        = self::TIMEOUT;
        $options[\CURLOPT_HTTPHEADER]     = $headers;

        return ['options' => $options, 'url' => $url];
    }

    public function execute($options, $url)
    {
        $message    = null;
        $resultCode = 0;
        $errorNo    = 0;

        $headers        = [];
        $headerCallback = function ($curl, $line) use (&$headers) {
            if (false === \strpos($line, ':')) {
                return \strlen($line);
            }
            list($key, $value)                 = \explode(':', \trim($line), 2);
            $headers[\strtolower(\trim($key))] = \trim($value);

            return \strlen($line);
        };
        $options[\CURLOPT_HEADERFUNCTION] = $headerCallback;

        $this->reset();
        \curl_setopt_array($this->curl, $options);
        $body = \curl_exec($this->curl);

        if (false === $body) {
            $message = \curl_error($this->curl);
            $errorNo = \curl_errno($this->curl);
        } else {
            $resultCode = \curl_getinfo($this->curl, \CURLINFO_HTTP_CODE);
        }

        $this->close();

        if (false === $body) {
            $body = $this->handleError($url, $errorNo, $message);
        }

        return ['code' => $resultCode, 'headers' => $headers, 'body' => $body];
    }

    private function handleError($url, $errorNo, $originalMessage)
    {
        switch ($errorNo) {
            case \CURLE_COULDNT_CONNECT:
            case \CURLE_COULDNT_RESOLVE_HOST:
            case \CURLE_OPERATION_TIMEOUTED:
                $message = 'Could not connect to API server '.$url;
                break;

            case \CURLE_SSL_CACERT:
            case \CURLE_SSL_PEER_CERTIFICATE:
                $message = 'Could not verify API SSL certificate with url: '.$url;
                break;

            default:
                $message = 'Unexpected error';
        }

        $message .= "\n\n\nNetwork error [errno - ".$errorNo.']: '.$originalMessage.')';

        return $message;
    }
}
