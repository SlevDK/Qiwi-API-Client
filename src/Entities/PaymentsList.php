<?php

namespace SlevDK\QiwiApi\Entities;


class PaymentsList extends RequestEntity
{
    /** @var string Request uri */
    public $uri = "payment-history/v2/persons/{wallet}/payments";

    /**
     * Prepare params for request
     *
     * @param array $options Request data params
     * @param string $token Qiwi wallet token
     *
     * @return array
     */
    protected function prepareParams($options, $token)
    {
        $params["query"] = $options;
        $params["headers"] = $this->prepareHeaders($token);
        
        return $params;
    }

    /**
     * Prepare resource URI
     *
     * @param string $baseURI Base uri
     * @param string $wallet Qiwi wallet number
     *
     * @return string
     */
    protected function prepareUri($baseURI, $wallet)
    {
        return $baseURI.str_replace("{wallet}", $wallet, $this->uri);
    }
}