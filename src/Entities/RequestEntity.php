<?php

namespace QiwiApi\Entities;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use QiwiApi\Exceptions\EmptyResponseException;
use QiwiApi\Exceptions\QiwiResponseException;
use QiwiApi\Exceptions\QiwiTransferException;

/**
 * Class RequestEntity
 * @package QiwiApi\Entities
 */
abstract class RequestEntity
{
    /** @var string Request method (GET by default) */
    public $method = "GET";

    /**
     * Execute request
     *
     * @param array $args Request data params
     * @param mixed $wallet Qiwi wallet number
     * @param string $token Qiwi wallet token
     * @param string $baseURI Qiwi api base uri
     * @param ClientInterface $http_client Http client
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function exec($args, $wallet, $token, $baseURI, ClientInterface $http_client)
    {
        $uri = $this->prepareUri($baseURI, $wallet);
        $params = $this->prepareParams($args, $token);

        return $this->sendRequest($this->method, $uri, $params, $http_client);
    }

    /**
     * Send request, return response or determine error and throw correct exception
     *
     *
     * @param string $method Request method
     * @param string $uri Request URI
     * @param array $params Request params
     * @param ClientInterface $client Http client (instance of ClientInterface)
     *
     * @return array Server response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest($method, $uri, $params, ClientInterface $client)
    {
        try {
            $response = $client->request($method, $uri, $params);

            return json_decode($response->getBody(), true);
        } catch(RequestException $e) {
            $this->handleException($e);
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Determine transfer or response exception type and throw correct one
     * 
     * @param RequestException $e Request exception
     * 
     * @throws \Exception
     */
    private function handleException(RequestException $e)
    {
        $handler_context = $e->getHandlerContext();

        // for curl exception
        if(!empty($handler_context)) {
            throw new QiwiTransferException("Something going wrong (cUrl error number {$handler_context["errno"]})");
        }

        throw new QiwiResponseException($e->getMessage());
    }

    /**
     * Return headers array
     *
     * @param string $token Qiwi wallet token
     * 
     * @return array
     */
    protected function prepareHeaders($token)
    {
        return [
            "Accept" => "application/json",
            "Content-type" => "application/json",
            "Authorization" => "Bearer ".$token
        ];
    }

    /**
     * Prepare params for request
     *
     * @param array $options Request params
     * @param string $token Qiwi wallet token
     *
     * @return array
     */
    abstract protected function prepareParams($options, $token);

    /**
     * Prepare uri
     *
     * @param string $baseURI ase uri
     * @param string $wallet qiwi wallet number
     *
     * @return string
     */
    abstract protected function prepareUri($baseURI, $wallet);
}