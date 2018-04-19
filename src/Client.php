<?php

namespace QiwiApi;

use GuzzleHttp\ClientInterface;
use QiwiApi\Entities\RequestEntity;

/**
 * Class Client
 * @package QiwiApi
 *
 * @see https://developer.qiwi.com/ru/qiwi-wallet-personal/ for correct request parameters
 * @method RequestEntity getProfile(array $params)
 * @method RequestEntity getPaymentsList(array $params)
 */
class Client
{
    /**
     * Qiwi wallet number
     *
     * @var mixed
     */
    private $wallet;

    /**
     * Qiwi wallet token
     *
     * @var string
     */
    private $token;

    /**
     * HTTP Client
     *
     * @var \GuzzleHttp\Client
     */
    private $http_client;

    /**
     * Base URL
     */
    private $baseURI = "https://edge.qiwi.com/";

    private $methodMap = [
        "getProfile" => "Profile",
        "getPaymentsList" => "PaymentsList"
    ];


    /**
     * Client constructor.
     *
     * @param mixed $wallet wallet number
     * @param string $token wallet token
     * @param ClientInterface $client   http client (\GuzzleHttp\Client by default)
     */
    public function __construct($wallet, string $token, ClientInterface $client = null)
    {
        $this->wallet   = $this->escapePlus($wallet);
        $this->token    = $token;

        if($client) {
            $this->http_client = $client;
        } else {
            $this->http_client = new \GuzzleHttp\Client();
        }
    }

    /**
     * Determine object which contains called method and call it, or throw exception
     *
     * @param string $name
     * @param array $args
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        $namespace_prefix = "QiwiApi\\Entities\\";
        if(array_key_exists($name, $this->methodMap)) {
            $obj_name = $namespace_prefix.$this->methodMap[$name];
            $object = new $obj_name();
            
            return $object->exec($args[0], $this->wallet, $this->token, $this->baseURI, $this->http_client);
        }

        throw new \BadMethodCallException("Qiwi API Client: Call to undefined method {$name}()");
    }

    /**
     * Escape "+" in wallet number (if exist)
     *
     * @param mixed $wallet wallet number
     *
     * @return mixed
     */
    private function escapePlus($wallet)
    {
        if(gettype($wallet) == "string") {
            return str_replace("+", "", $wallet);
        }

        return $wallet;
    }
}