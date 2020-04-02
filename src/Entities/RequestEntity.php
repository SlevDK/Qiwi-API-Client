<?php

namespace SlevDK\QiwiApi\Entities;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use SlevDK\QiwiApi\Exceptions\BadRequestException;
use SlevDK\QiwiApi\Exceptions\NotFoundException;
use SlevDK\QiwiApi\Exceptions\QiwiException;
use SlevDK\QiwiApi\Exceptions\ResponseException;
use SlevDK\QiwiApi\Exceptions\TransferException;

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
     * @throws QiwiException
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
     * @param string $method Request method
     * @param string $uri Request URI
     * @param array $params Request params
     * @param ClientInterface $client Http client (instance of ClientInterface)
     *
     * @return mixed Server response
     * @throws QiwiException
     */
    protected function sendRequest($method, $uri, $params, ClientInterface $client)
    {
        try {
            $response = $client->request($method, $uri, $params);
        } catch(RequestException $e) {
            // handle transfer or response exception
            // response exception will be thrown if http client doesn't process 400+ status code
            // as ordinary response ('http_errors' => true)
            $this->handleException($e);
        }

        // handle 400+ status codes
        if ($response->getStatusCode() >= 400) {
            $this->handleHttpError($response);
        }

        return $this->parseResponse($response);
    }

    /**
     * Parse and return response content
     * Depends on content type
     *
     * By default - return decoded json string
     *
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function parseResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Determine transfer or response exception type and throw correct one
     * 
     * @param RequestException $e Request exception
     * 
     * @throws QiwiException
     */
    private function handleException(RequestException $e)
    {
        $handler_context = $e->getHandlerContext();

        // for curl exception
        if(!empty($handler_context)) {
            throw new TransferException(
                "Something going wrong (cUrl error number {$handler_context["errno"]})",
                $e->getCode(),
                $e->getPrevious()
            );
        }

        throw new ResponseException($e->getMessage(), $e->getCode(), $e->getPrevious());
    }

    /**
     * Handle 400 and 404 response status codes
     * Qiwi API return 400 code for validation error
     * and 404 if resource not found
     *
     * @param ResponseInterface $response Qiwi response
     * @throws QiwiException
     */
    private function handleHttpError(ResponseInterface $response)
    {
        $code = $response->getStatusCode();
        $content = $response->getBody()->getContents();

        if ($code == 400) {
            throw new BadRequestException($content, $code);
        } else if ($code == 404) {
            throw new NotFoundException($content, $code);
        }
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
    protected function prepareParams($options, $token) {
        $params["query"] = $options;
        $params["headers"] = $this->prepareHeaders($token);

        return $params;
    }

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