<?php

namespace SlevDK\QiwiApi\Entities;

class PaymentsTotal extends RequestEntity
{
    /** @var string Request uri */
    public $uri = "payment-history/v2/persons/{wallet}/payments/total";

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