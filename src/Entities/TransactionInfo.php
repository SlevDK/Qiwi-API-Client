<?php


namespace SlevDK\QiwiApi\Entities;


use GuzzleHttp\ClientInterface;
use SlevDK\QiwiApi\Exceptions\InvalidArgumentsException;
use SlevDK\QiwiApi\Exceptions\QiwiException;

class TransactionInfo extends RequestEntity
{
    /** @var string Request uri */
    public $uri = "payment-history/v2/transactions/{transaction_id}";

    /**
     * @inheritDoc
     */
    protected function prepareUri($baseURI, $transaction_id)
    {
        return str_replace('{transaction_id}',$transaction_id, $baseURI.$this->uri);
    }

    /**
     * Overridden execute method
     *
     * @param array $args
     * @param mixed $wallet
     * @param string $token
     * @param string $baseURI
     * @param ClientInterface $http_client
     * @return array
     * @throws QiwiException
     */
    public function exec($args, $wallet, $token, $baseURI, ClientInterface $http_client)
    {
        // transaction_id required
        if (!isset($args['transaction_id'])) {
            throw new InvalidArgumentsException('Transaction ID is missing');
        }

        return parent::exec($args, $args['transaction_id'], $token, $baseURI, $http_client);
    }
}