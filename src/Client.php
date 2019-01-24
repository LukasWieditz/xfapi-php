<?php

namespace XFApi;

use GuzzleHttp\Client as GuzzleClient;
use XFApi\Container\AbstractContainer;
use XFApi\Container\DBTech\eCommerceContainer as DBTecheCommeceContainer;
use XFApi\Container\XFContainer;
use XFApi\Container\XFRMContainer;
use XFApi\Exception\XFApiException;

/**
 * Class Client
 * @package XFApi
 *
 * @property XFContainer $xf
 * @property XFRMContainer $xfrm
 * @property DBTecheCommeceContainer $dbtech_ecommerce
 */
class Client
{
    const LIBRARY_VERSION = '1.0.0 Alpha 1';

    protected $apiUrl;
    protected $apiKey;
    protected $apiUserId;
    protected $httpClient;

    protected $_container = [
        'xf' => XFContainer::class,
        'xfrm' => XFRMContainer::class,
        'dbtech_ecommerce' => DBTecheCommeceContainer::class
    ];

    protected $_containerCache = [];

    /**
     * Client constructor.
     *
     * @param $apiUrl
     * @param $apiKey
     * @param string|null $apiUserId
     */
    public function __construct($apiUrl, $apiKey, $apiUserId = null)
    {
        $this->setApiUrl($apiUrl);
        $this->setApiKey($apiKey);
        $this->setApiUserId($apiUserId);

        $this->setHttpClient(new GuzzleClient);
    }

    /**
     * @return GuzzleClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param GuzzleClient $httpClient
     */
    public function setHttpClient(GuzzleClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiUserId
     */
    public function setApiUserId($apiUserId)
    {
        $this->apiUserId = $apiUserId;
    }

    /**
     * @return string
     */
    public function getApiUserId()
    {
        return $this->apiUserId;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param $endpoint
     * @param array $params
     * @return string
     */
    public function getFullUrl($endpoint, array $params = [])
    {
        if (substr($endpoint, 0, 1) !== '/') {
            $endpoint = '/' . $endpoint;
        }

        if (!empty($params)) {
            $paramsStr = http_build_query($params);
            if (strpos($endpoint, '?') === false) {
                $endpoint .= '?';
            } else {
                $endpoint .= '&';
            }
            $endpoint .= $paramsStr;
        }

        return $this->getApiUrl() . $endpoint;
    }

    /**
     * @param $endpoint
     * @param array $params
     * @param array $headers
     * @return array
     * @throws XFApiException
     */
    public function get($endpoint, array $params = [], array $headers = [])
    {
        return $this->request('GET', $endpoint, $params, [], $headers);
    }

    /**
     * @param $endpoint
     * @param array $params
     * @param array $data
     * @param array $headers
     *
     * @return array
     * @throws XFApiException
     */
    public function post($endpoint, array $params = [], array $data = [], array $headers = [])
    {
        return $this->request('POST', $endpoint, $params, $data, $headers);
    }

    /**
     * @param $endpoint
     * @param array $params
     * @param array $data
     * @param array $headers
     * @return array
     * @throws XFApiException
     */
    public function put($endpoint, array $params = [], array $data = [], array $headers = [])
    {
        return $this->request('POST', $endpoint, $params, $data, $headers);
    }

    /**
     * @param $endpoint
     * @param array $params
     * @param array $data
     * @param array $headers
     * @return array
     * @throws XFApiException
     */
    public function patch($endpoint, array $params = [], array $data = [], array $headers = [])
    {
        return $this->request('POST', $endpoint, $params, $data, $headers);
    }

    /**
     * @param $endpoint
     * @param array $params
     * @param array $headers
     * @return array
     * @throws XFApiException
     */
    public function delete($endpoint, array $params = [], array $headers = [])
    {
        return $this->request('DELETE', $endpoint, $params, [], $headers);
    }

    /**
     * @param $method
     * @param $endpoint
     * @param array $params
     * @param array $data
     * @param array $headers
     * @return array
     *
     * @throws XFApiException
     */
    public function request($method, $endpoint, array $params = [], array $data = [], array $headers = [])
    {
        $headers = array_merge($headers, [
            'XF-Api-Key' => $this->getApiKey(),
            'User-Agent' => 'xfapi-php/' . self::LIBRARY_VERSION .
                ' (PHP ' . phpversion() . ')',
            'Accept-Charset' => 'utf-8',
        ]);

        $userId = $this->getApiUserId();
        if ($userId) {
            $headers['XF-Api-User'] = $userId;
        }

        if (!isset($headers['Accept'])) {
            $headers['Accept'] = 'application/json';
        }

        $requestOptions = [
            'http_errors' => false,
            'headers' => $headers,
        ];

        if (strtolower($method) === 'post') {
            $requestOptions['form_params'] = $data;
        }

        try {
            $request = $this->getHttpClient()->request($method, $this->getFullUrl($endpoint, $params), $requestOptions);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // this won't trigger, exceptions are disabled.
            // only have this here so phpStorm stops fussing
            // but just in case...
            throw new XFApiException($e->getMessage());
        }

        switch ($request->getStatusCode()) {
            case 200:
                /** @noinspection PhpComposerExtensionStubsInspection */
                return json_decode($request->getBody()->getContents(), true);
            default:
                // todo: implement exceptions for different possible error codes.
                throw new XFApiException('HTTP Error code: ' . $request->getStatusCode());
        }
    }

    /**
     * @param string $name
     * @return AbstractContainer
     * @throws XFApiException
     */
    public function __get($name)
    {
        if (isset($this->_container[$name])) {
            if (!isset($this->_containerCache[$name])) {
                $class = $this->_container[$name];
                $this->_containerCache[$name] = new $class($this);
            }

            return $this->_containerCache[$name];
        }

        $method = 'get' . $this->camelCase($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new XFApiException('Unable to find container ' . $name);
    }

    protected function camelCase($string, $glue = '_')
    {
        return str_replace(' ', '', ucwords(str_replace($glue, ' ', $string)));
    }
}