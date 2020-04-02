<?php

namespace SlevDK\QiwiApi\Entities;

class Profile extends RequestEntity
{
    /** @var string Request uri */
    public $uri = "person-profile/v1/profile/current";

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
        return $baseURI.$this->uri;
    }
}