<?php


namespace SlevDK\QiwiApi\Entities;


class AccountsList extends RequestEntity
{
    /** @var string Request uri */
    public $uri = "funding-sources/v2/persons/{wallet}/accounts";

    /**
     * @inheritDoc
     */
    protected function prepareUri($baseURI, $wallet)
    {
        return $baseURI.str_replace('{wallet}', $wallet, $this->uri);
    }
}