<?php
namespace Elastification\Client\Transport\HttpGuzzle;

use Elastification\Client\Transport\TransportRequestInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Stream\Stream;

/**
 * @package Elastification\Client\Transport\HttpGuzzle
 * @author  Mario Mueller
 */
class GuzzleTransportRequest implements TransportRequestInterface
{
    /**
     * @var RequestInterface
     */
    private $guzzleRequest;

    /**
     * @param RequestInterface $guzzleRequest
     */
    public function __construct(RequestInterface $guzzleRequest)
    {
        $this->guzzleRequest = $guzzleRequest;
    }

    /**
     * @param string $body The raw request body.
     *
     * @return void
     * @author Mario Mueller
     */
    public function setBody($body)
    {
        $this->guzzleRequest->setBody(Stream::factory($body));
    }

    /**
     * @param string $path The path according to the Elasticsearch http interface.
     *
     * @return void
     * @author Mario Mueller
     */
    public function setPath($path)
    {
        $this->guzzleRequest->setPath($path);
    }

    /**
     * @return RequestInterface
     * @author Mario Mueller
     */
    public function getWrappedRequest()
    {
        return $this->guzzleRequest;
    }

    /**
     * @param array $params
     *
     * @return void
     * @author Mario Mueller
     */
    public function setQueryParams(array $params)
    {
        $this->guzzleRequest->setQuery($params);
    }
}
