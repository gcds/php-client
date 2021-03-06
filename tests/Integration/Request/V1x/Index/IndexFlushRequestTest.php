<?php
namespace Elastification\Client\Tests\Integration\Request\V1x\Index;


use Elastification\Client\Request\V1x\Index\IndexFlushRequest;
use Elastification\Client\Response\V1x\Index\RefreshIndexResponse;
use Elastification\Client\Tests\Integration\Request\V1x\AbstractElastic;

class IndexFlushRequestTest extends AbstractElastic
{
    const TYPE = 'request-index-flush';

    public function testIndexFlush()
    {
        $this->createIndex();
        $this->createDocument(self::TYPE);

        $flushIndexRequest = new IndexFlushRequest(ES_INDEX, null, $this->getSerializer());

        /** @var RefreshIndexResponse $response */
        $response = $this->getClient()->send($flushIndexRequest);

        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));
    }

}
