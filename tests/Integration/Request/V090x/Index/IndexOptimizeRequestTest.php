<?php
namespace Elastification\Client\Tests\Integration\Request\V090x\Index;


use Elastification\Client\Request\V090x\Index\IndexOptimizeRequest;
use Elastification\Client\Response\V090x\Index\RefreshIndexResponse;
use Elastification\Client\Tests\Integration\Request\V090x\AbstractElastic;

class IndexOptimizeRequestTest extends AbstractElastic
{
    const TYPE = 'request-index-optimize';

    public function testIndexOptimize()
    {
        $this->createIndex();
        $this->createDocument(self::TYPE);

        $refreshIndexRequest = new IndexOptimizeRequest(ES_INDEX, null, $this->getSerializer());

        /** @var RefreshIndexResponse $response */
        $response = $this->getClient()->send($refreshIndexRequest);

        $this->assertTrue($response->isOk());
        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));
    }

}
