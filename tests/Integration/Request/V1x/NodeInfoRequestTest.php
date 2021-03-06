<?php
namespace Elastification\Client\Tests\Integration\Request\V1x;

use Elastification\Client\Request\V1x\NodeInfoRequest;
use Elastification\Client\Response\V1x\NodeInfoResponse;

class NodeInfoRequestTest extends AbstractElastic
{

    const TYPE = 'request-node-info';

    public function testNodeInfo()
    {
        $countRequest = new NodeInfoRequest($this->getSerializer());

        /** @var NodeInfoResponse $response */
        $response = $this->getClient()->send($countRequest);

        $this->assertNotEmpty($response->getClusterName());
        $this->assertNotEmpty($response->getName());
        $this->assertNotEmpty($response->getTagline());
        $this->assertSame(200, $response->getStatus());
        $this->assertArrayHasKey('number', $response->getVersion());
    }


}
