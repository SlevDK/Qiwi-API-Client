<?php

namespace QiwiApi\Entities;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class RequestEntity
 * @package QiwiApi\Entities
 */
abstract class RequestEntity
{
    /** @var array Common API error status codes */
    private $status_code_exceptions = [
        400 => [
            "ex" => "ArgumentException",
            "descr" => "Wrong query data!"
        ],
        401 => [
            "ex" => "UnauthorizedException",
            "descr" => "Wrong token or token live expired!"
        ],
        403 => [
            "ex" => "TokenLowRightsException",
            "descr" => "Token has too low rights for this request!"
        ],
        504 => [
            "ex" => "GatewayTimeOutException",
            "descr" => "Gateway time-out, try later!"
        ]
    ];

    private $curl_errno_exception = [
        52 => [
            "ex" => "EmptyResponseException",
            "descr" => "Server return empty response!"
        ]
    ];

    /** @var array Personal API error status codes */
    public $personal_status_code_exceptions = [];
    
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
     * @throws \Exception
     */
    protected function sendRequest($method, $uri, $params, ClientInterface $client)
    {
        try {
            $response = $client->request($method, $uri, $params);
        } catch(RequestException $e) {
            $this->handleException($e);
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Determine error by curl or http error status code and throw correct exception
     * 
     * @param RequestException $e Request exception
     * 
     * @throws \Exception
     */
    private function handleException(RequestException $e)
    {
        $namespace_prefix = "QiwiApi\\Exceptions\\";

        $handler_context = $e->getHandlerContext();

        // for curl exception
        if(
            !empty($handler_context) &&
            array_key_exists($handler_context["errno"], $this->curl_errno_exception)
        ) {
            $ex = $namespace_prefix.$this->curl_errno_exception[$handler_context["errno"]]["ex"];
            $descr = $this->curl_errno_exception[$handler_context["errno"]]["descr"];

            throw new $ex($descr);
        }

        $status_code = $e->getResponse()->getStatusCode();
        $descr = "Qiwi API returned {$status_code} status code: ";

        // get bad status code
        if(array_key_exists($status_code, $this->status_code_exceptions)) {

            $ex = $namespace_prefix.$this->status_code_exceptions[$status_code]["ex"];
            $descr .= $this->status_code_exceptions[$status_code]["descr"];

            Throw new $ex($descr);

        } else if(array_key_exists($status_code, $this->personal_status_code_exceptions)) {

            $ex = $namespace_prefix.$this->personal_status_code_exceptions[$status_code]["ex"];
            $descr .= $this->personal_status_code_exceptions[$status_code]["descr"];

            Throw new $ex($descr);

        } else {
            Throw new \Exception($e->getMessage());
        }
    }

    /**
     * Return headers array
     * 
     * @param array $params Request params
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

    abstract protected function prepareParams($params, $token);

    abstract protected function prepareUri($baseURI, $wallet);
}