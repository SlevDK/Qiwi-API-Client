<?php
/**
 * Created by PhpStorm.
 * User: slevin
 * Date: 08.05.18
 * Time: 0:23
 */

namespace QiwiApi\Entities;


use QiwiApi\Exceptions\ArgumentException;

class PaymentsTotal extends RequestEntity
{
    /** @var string Request uri */
    public $uri = "payment-history/v2/persons/{wallet}/payments/total";

    /**
     * Prepare params for request
     *
     * @param array $options Request data params
     * @param string $token Qiwi wallet token
     *
     * @return array
     * @throws ArgumentException
     */
    protected function prepareParams($options, $token)
    {
        $params["query"] = $this->checkRequiredParams($options);
        $params["headers"] = $this->prepareHeaders($token);

        return $params;
    }

    /**
     * Check required params
     *
     * @param array $options
     *
     * @return array
     * @throws ArgumentException
     */
    private function checkRequiredParams($options)
    {
        if(!isset($options["startDate"]))
            throw new ArgumentException("Required param 'startDate' missing");

        if(!isset($options["endDate"]))
            throw new ArgumentException("Required param 'endDate' missing");

        // Check date format by converting into DateTime, add TimeZone
        try {
            $startDate = new \DateTime(
                $options["startDate"],
                new \DateTimeZone((isset($option["TimeZone"])) ? $options["TimeZone"] : "Europe/Moscow"));

            $endDate = new \DateTime(
                $options["endDate"],
                new \DateTimeZone((isset($option["TimeZone"])) ? $options["TimeZone"] : "Europe/Moscow"));
        } catch(\Exception $e) {
            throw new ArgumentException("Wrong date format");
        }


        $options["startDate"] = $startDate->format(DATE_ATOM);
        $options["endDate"] = $endDate->format(DATE_ATOM);
        var_dump($options);

        return $options;
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