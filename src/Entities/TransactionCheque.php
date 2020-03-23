<?php


namespace SlevDK\QiwiApi\Entities;

use Psr\Http\Message\ResponseInterface;
use SlevDK\QiwiApi\Exceptions\ResponseException;

class TransactionCheque extends TransactionInfo
{
    /** @var string Request uri */
    public $uri = "payment-history/v1/transactions/{transaction_id}/cheque/file";

    /**
     * Reloaded parser method
     *
     * @param ResponseInterface $response
     * @return mixed|void
     * @throws ResponseException
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $content_type = $response->getHeaderLine('Content-Type');
        $content_len = $response->getHeaderLine('Content-Length');

        // if content stream not readable, throw exception instead of return null
        if (!$response->getBody()->isReadable()) {
            throw new ResponseException('Response stream is not readable');
        }

        // read binary data
        $image_binary = $response->getBody()->read($content_len);

        return [
            'content_type' => $content_type,
            'content_length' => $content_len,
            'data' => $image_binary
        ];
    }
}