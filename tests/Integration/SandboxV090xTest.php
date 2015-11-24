<?php
namespace Elastification\Client\Tests\Integration;

use Elastification\Client\Exception\ClientException;
use Elastification\Client\Request\RequestManager;
use Elastification\Client\Request\RequestManagerInterface;
use Elastification\Client\Request\V090x\AliasesRequest;
use Elastification\Client\Request\V090x\CountRequest;
use Elastification\Client\Request\V090x\CreateDocumentRequest;
use Elastification\Client\Request\V090x\DeleteByQueryRequest;
use Elastification\Client\Request\V090x\DeleteDocumentRequest;
use Elastification\Client\Request\V090x\DeleteSearchRequest;
use Elastification\Client\Request\V090x\DeleteTemplateRequest;
use Elastification\Client\Request\V090x\GetDocumentRequest;
use Elastification\Client\Request\V090x\GetTemplateRequest;
use Elastification\Client\Request\V090x\Index\CacheClearRequest;
use Elastification\Client\Request\V090x\Index\CreateIndexRequest;
use Elastification\Client\Request\V090x\Index\CreateMappingRequest;
use Elastification\Client\Request\V090x\CreateTemplateRequest;
use Elastification\Client\Request\V090x\Index\CreateWarmerRequest;
use Elastification\Client\Request\V090x\Index\DeleteIndexRequest;
use Elastification\Client\Request\V090x\Index\DeleteMappingRequest;
use Elastification\Client\Request\V090x\Index\DeleteWarmerRequest;
use Elastification\Client\Request\V090x\Index\GetAliasesRequest;
use Elastification\Client\Request\V090x\Index\GetMappingRequest;
use Elastification\Client\Request\V090x\Index\GetWarmerRequest;
use Elastification\Client\Request\V090x\Index\IndexExistsRequest;
use Elastification\Client\Request\V090x\Index\IndexFlushRequest;
use Elastification\Client\Request\V090x\Index\IndexOptimizeRequest;
use Elastification\Client\Request\V090x\Index\IndexSegmentsRequest;
use Elastification\Client\Request\V090x\Index\IndexSettingsRequest;
use Elastification\Client\Request\V090x\Index\IndexStatsRequest;
use Elastification\Client\Request\V090x\Index\IndexStatusRequest;
use Elastification\Client\Request\V090x\Index\IndexTypeExistsRequest;
use Elastification\Client\Request\V090x\Index\RefreshIndexRequest;
use Elastification\Client\Request\V090x\Index\UpdateAliasesRequest;
use Elastification\Client\Request\V090x\NodeInfoRequest;
use Elastification\Client\Request\V090x\SearchRequest;
use Elastification\Client\Request\V090x\UpdateDocumentRequest;
use Elastification\Client\Response\Response;
use Elastification\Client\Response\ResponseInterface;
use Elastification\Client\Response\V090x\CountResponse;
use Elastification\Client\Response\V090x\CreateUpdateDocumentResponse;
use Elastification\Client\Response\V090x\DocumentResponse;
use Elastification\Client\Response\V090x\Index\IndexResponse;
use Elastification\Client\Response\V090x\Index\IndexStatsResponse;
use Elastification\Client\Response\V090x\Index\IndexStatusResponse;
use Elastification\Client\Response\V090x\Index\RefreshIndexResponse;
use Elastification\Client\Response\V090x\NodeInfoResponse;
use Elastification\Client\Response\V090x\SearchResponse;
use Elastification\Client\Serializer\NativeJsonSerializer;
use Elastification\Client\Serializer\SerializerInterface;
use Elastification\Client\Transport\HttpGuzzle\GuzzleTransport;
use Elastification\Client\Transport\TransportInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Elastification\Client\Client;
use Elastification\Client\ClientInterface;

/**
 * @group es_090
 */
class SandboxV090xTest extends \PHPUnit_Framework_TestCase
{

    const INDEX = 'dawen-elastic';
    const TYPE = 'sandbox';

    private $url = 'http://127.0.01:9200/';
//    private $url = 'http://192.168.33.109:9200/';

    /**
     * @var GuzzleClientInterface
     */
    private $guzzleClient;

    /**
     * @var RequestManagerInterface
     */
    private $requestManager;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TransportInterface
     */
    private $transportClient;


    protected function setUp()
    {
        parent::setUp();

        $this->guzzleClient = new GuzzleClient(array('base_uri' => $this->url));
        $this->transportClient = new GuzzleTransport($this->guzzleClient);
        $this->requestManager = new RequestManager();
        $this->client = new Client($this->transportClient, $this->requestManager);
        $this->serializer = new NativeJsonSerializer();
    }

    protected function tearDown()
    {
        parent::tearDown();

        if($this->hasIndex()) {
            $this->deleteIndex();
        }

        $this->guzzleClient = null;
        $this->requestManager = null;
        $this->client = null;
        $this->serializer = null;
        $this->transportClient = null;


    }


    public function testGetMappingWithType()
    {
        $this->createIndex();
        $data = array('name' => 'test' . rand(100, 10000), 'value' => 'myTestVal' . rand(100, 10000));
        $this->createDocument($data);
        $this->refreshIndex();

        $timeStart = microtime(true);

        $getMappingRequest = new GetMappingRequest(self::INDEX, self::TYPE, $this->serializer);

        /** @var ResponseInterface $response */
        $response = $this->client->send($getMappingRequest);

        echo 'getMapping: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertContains(self::TYPE, $response->getRawData());
        $this->assertContains('properties', $response->getRawData());
    }

    public function testGetMappingWithoutType()
    {
        $this->createIndex();
        $data = array('name' => 'test' . rand(100, 10000), 'value' => 'myTestVal' . rand(100, 10000));
        $this->createDocument($data);
        $this->refreshIndex();

        $timeStart = microtime(true);

        $getMappingRequest = new GetMappingRequest(self::INDEX, null, $this->serializer);

        /** @var ResponseInterface $response */
        $response = $this->client->send($getMappingRequest);

        echo 'getMapping(without type): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertContains(self::INDEX, $response->getRawData());
        $this->assertContains(self::TYPE, $response->getRawData());
        $this->assertContains('properties', $response->getRawData());
    }


    public function testIndexTypeExists()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();

        $timeStart = microtime(true);

        $indexExistsRequest = new IndexTypeExistsRequest(self::INDEX, self::TYPE, $this->serializer);

        /** @var ResponseInterface $response */
        $response = $this->client->send($indexExistsRequest);

        echo 'indexTypeExists: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertInstanceOf('Elastification\Client\Response\Response', $response);
    }

    public function testIndexTypeExistsNotExisting()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();

        $timeStart = microtime(true);

        $indexExistsRequest = new IndexTypeExistsRequest(self::INDEX, 'not-existing-type', $this->serializer);

        try {
            $this->client->send($indexExistsRequest);
        } catch(ClientException $exception) {
            echo 'indexTypeExists(not existing): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;
            $this->assertContains('Not Found', $exception->getMessage());
            return;
        }

        $this->fail();
    }

    public function testIndexStatsWithIndex()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();


        $timeStart = microtime(true);

        $indexStatsRequest = new IndexStatsRequest(self::INDEX, null, $this->serializer);

        /** @var IndexStatsResponse $response */
        $response = $this->client->send($indexStatsRequest);

        echo 'indexStats(with index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());

        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));

        $all = $response->getAll();
        $this->assertTrue(isset($all['primaries']));
        $this->assertTrue(isset($all['total']));

        $indices = $response->getIndices();
        $this->assertTrue(isset($indices[self::INDEX]));
    }

    public function testIndexStatsWithoutIndex()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();


        $timeStart = microtime(true);

        $indexStatsRequest = new IndexStatsRequest(null, null, $this->serializer);

        /** @var IndexStatsResponse $response */
        $response = $this->client->send($indexStatsRequest);

        echo 'indexStats(without index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());

        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));

        $all = $response->getAll();
        $this->assertTrue(isset($all['primaries']));
        $this->assertTrue(isset($all['total']));

        $indices = $response->getIndices();
        $this->assertTrue(isset($indices[self::INDEX]));
    }

    public function testIndexStatusWithIndex()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();


        $timeStart = microtime(true);

        $indexStatsRequest = new IndexStatusRequest(self::INDEX, null, $this->serializer);

        /** @var IndexStatusResponse $response */
        $response = $this->client->send($indexStatsRequest);

        echo 'indexStatus(with index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());

        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));

        $indices = $response->getIndices();
        $this->assertTrue(isset($indices[self::INDEX]));
    }

    public function testIndexStatusWithoutIndex()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();


        $timeStart = microtime(true);

        $indexStatsRequest = new IndexStatusRequest(null, null, $this->serializer);

        /** @var IndexStatusResponse $response */
        $response = $this->client->send($indexStatsRequest);

        echo 'indexStatus(with index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());

        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));

        $indices = $response->getIndices();
        $this->assertTrue(isset($indices[self::INDEX]));
    }

    public function testCreateMappingWithIndexAndType()
    {
        $this->createIndex();

        $timeStart = microtime(true);

        $mapping = [
            self::TYPE => [
                'properties' => [
                    'message' => ['type' => 'string']
                ]
            ]
        ];

        $createMappingRequest = new CreateMappingRequest(self::INDEX , self::TYPE, $this->serializer);
        $createMappingRequest->setBody($mapping);

        /** @var IndexResponse $response */
        $response = $this->client->send($createMappingRequest);

        echo 'createMapping(with index,type): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());
        $this->assertTrue($response->acknowledged());

        //check if exists
        $getMappingRequest = new GetMappingRequest(self::INDEX, self::TYPE, $this->serializer);

        /** @var ResponseInterface $getMappingResponse */
        $getMappingResponse = $this->client->send($getMappingRequest);
        $data = $getMappingResponse->getData();

        $this->assertTrue(isset($data[self::TYPE]));
        $this->assertTrue(isset($data[self::TYPE]['properties']));
        $this->assertTrue(isset($data[self::TYPE]['properties']['message']));
        $this->assertTrue(isset($data[self::TYPE]['properties']['message']['type']));
        $this->assertSame('string', $data[self::TYPE]['properties']['message']['type']);
        //the not activated assertSame is for tessting it when Gateway is fixed.
//        $this->assertSame($mapping[self::TYPE], $data[self::TYPE]);
    }

    public function testDeleteMappingWithIndexAndType()
    {
        $this->createIndex();
        $mapping = [
            self::TYPE => [
                'properties' => [
                    'message' => ['type' => 'string']
                ]
            ]
        ];

        $createMappingRequest = new CreateMappingRequest(self::INDEX , self::TYPE, $this->serializer);
        $createMappingRequest->setBody($mapping);

        $this->client->send($createMappingRequest);

        $timeStart = microtime(true);

        $deleteMappingRequest = new DeleteMappingRequest(self::INDEX , self::TYPE, $this->serializer);

        /** @var IndexResponse $response */
        $response = $this->client->send($deleteMappingRequest);

        $this->assertTrue($response->isOk());
        $this->assertTrue($response->acknowledged());

        echo 'deleteMapping(with index,type): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        //check if exists
        $getMappingRequest = new GetMappingRequest(self::INDEX, self::TYPE, $this->serializer);

        try {
            $this->client->send($getMappingRequest);
        } catch (ClientException $exception) {
            $this->assertContains('Not Found', $exception->getMessage());
            return;
        }

        $this->fail();
    }

    public function testIndexSettingsWithIndex()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();


        $timeStart = microtime(true);

        $indexStatsRequest = new IndexSettingsRequest(self::INDEX, null, $this->serializer);

        /** @var IndexStatsResponse $response */
        $response = $this->client->send($indexStatsRequest);

        echo 'indexSettings(with index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $data = $response->getData()->getGatewayValue();
        $this->assertArrayHasKey(self::INDEX, $data);
    }

    public function testIndexSegmentsWithIndex()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();

        $timeStart = microtime(true);

        $indexStatsRequest = new IndexSegmentsRequest(self::INDEX, null, $this->serializer);

        /** @var IndexStatusResponse $response */
        $response = $this->client->send($indexStatsRequest);

        echo 'indexSegments(with index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());

        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));

        $indices = $response->getIndices();
        $this->assertTrue(isset($indices[self::INDEX]));
    }

    public function testIndexSegmentsWithoutIndex()
    {
        $this->createIndex();
        $this->createDocument();
        $this->refreshIndex();

        $timeStart = microtime(true);

        $indexStatsRequest = new IndexSegmentsRequest(null, null, $this->serializer);

        /** @var IndexStatusResponse $response */
        $response = $this->client->send($indexStatsRequest);

        echo 'indexSegments(without index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());

        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));

        $indices = $response->getIndices();
        $this->assertTrue(isset($indices[self::INDEX]));
    }

    public function testClearCache()
    {
        $this->createIndex();
        $this->createDocument();
        $timeStart = microtime(true);

        $refreshIndexRequest = new CacheClearRequest(self::INDEX, null, $this->serializer);

        /** @var RefreshIndexResponse $response */
        $response = $this->client->send($refreshIndexRequest);

        echo 'clearCache: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());
        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));
    }

    public function testIndexOptimize()
    {
        $this->createIndex();
        $this->createDocument();
        $timeStart = microtime(true);

        $refreshIndexRequest = new IndexOptimizeRequest(self::INDEX, null, $this->serializer);

        /** @var RefreshIndexResponse $response */
        $response = $this->client->send($refreshIndexRequest);

        echo 'indexOptimize: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());
        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));
    }

    public function testIndexFlush()
    {
        $this->createIndex();
        $this->createDocument();
        $timeStart = microtime(true);

        $refreshIndexRequest = new IndexFlushRequest(self::INDEX, null, $this->serializer);

        /** @var RefreshIndexResponse $response */
        $response = $this->client->send($refreshIndexRequest);

        echo 'indexFlush: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($response->isOk());
        $shards = $response->getShards();
        $this->assertTrue(isset($shards['total']));
        $this->assertTrue(isset($shards['successful']));
        $this->assertTrue(isset($shards['failed']));
    }


    public function testGetAliasesWithoutIndex()
    {
        $this->createIndex();

        $aliases = [
            'actions' => [
                [
                    'add' => [
                        'index' => self::INDEX,
                        'alias' => 'alias-' . self::INDEX
                    ]
                ]
            ]
        ];

        $aliasesRequest = new AliasesRequest(null, null, $this->serializer);
        $aliasesRequest->setBody($aliases);

        /** @var IndexResponse $response */
        $response = $this->client->send($aliasesRequest);

        $timeStart = microtime(true);

        $getAliasesRequest = new GetAliasesRequest(null, null, $this->serializer);

        /** @var Response $response */
        $response = $this->client->send($getAliasesRequest);

        echo 'getAliases (without index): ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $data = $response->getData()->getGatewayValue();

        $this->assertArrayHasKey(self::INDEX, $data);
        $this->assertTrue(isset($data[self::INDEX]['aliases']['alias-' . self::INDEX]));
    }

    public function testCreateDeleteWarmer()
    {
        $index = 'warmer-index';

        if(!$this->hasIndex($index)) {
            $this->createIndex($index);
        }

        $this->refreshIndex($index);
        sleep(1);

        $warmerName = 'test_warmer';

        $warmer = [
            'query' => [
                'match_all' => []
            ]
        ];

        $timeStart = microtime(true);

        $createWarmerRequest = new CreateWarmerRequest($index, null, $this->serializer);
        $createWarmerRequest->setWarmerName($warmerName);
        $createWarmerRequest->setBody($warmer);

        /** @var IndexResponse $createResponse */
        $createResponse = $this->client->send($createWarmerRequest);

        echo 'createWarmer: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($createResponse->isOk());
        $this->assertTrue($createResponse->acknowledged());

        $this->refreshIndex($index);
        sleep(1);

        $timeStart = microtime(true);

        $deleteRequest = new DeleteWarmerRequest($index, null, $this->serializer);
        $deleteRequest->setWarmerName($warmerName);

        $deleteResponse = $this->client->send($deleteRequest);

        echo 'deleteWarmer: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($deleteResponse->isOk());
        $this->assertTrue($deleteResponse->acknowledged());

        $this->deleteIndex($index);
    }

    public function testGetWarmer()
    {
        $index = 'warmer-index';

        if(!$this->hasIndex($index)) {
            $this->createIndex($index);
        }

        $this->refreshIndex($index);
        sleep(1);

        $warmerName = 'test_warmer';

        $warmer = [
            'query' => [
                'match_all' => []
            ]
        ];

        $timeStart = microtime(true);

        $createWarmerRequest = new CreateWarmerRequest($index, null, $this->serializer);
        $createWarmerRequest->setWarmerName($warmerName);
        $createWarmerRequest->setBody($warmer);

        /** @var IndexResponse $createResponse */
        $createResponse = $this->client->send($createWarmerRequest);

        echo 'createWarmer: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($createResponse->isOk());
        $this->assertTrue($createResponse->acknowledged());

        $this->refreshIndex($index);

        $getWarmerRequest = new GetWarmerRequest($index, null, $this->serializer);
        $getWarmerRequest->setWarmerName($warmerName);

        $getResponse = $this->client->send($getWarmerRequest);
        $data = $getResponse->getData()->getGatewayValue();
        $this->assertArrayHasKey($warmerName, $data['warmer-index']['warmers']);

        sleep(1);

        $timeStart = microtime(true);

        $deleteRequest = new DeleteWarmerRequest($index, null, $this->serializer);
        $deleteRequest->setWarmerName($warmerName);

        $deleteResponse = $this->client->send($deleteRequest);

        echo 'deleteWarmer: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $this->assertTrue($deleteResponse->isOk());
        $this->assertTrue($deleteResponse->acknowledged());

        $this->deleteIndex($index);
    }

    public function testUpdateAliases()
    {

        $this->createIndex();
        $data = array('name' => 'test', 'value' => 'myTestVal' . rand(100, 10000));
        $this->createDocument($data);
        $data = array('name' => 'test', 'value' => 'myTestVal' . rand(100, 10000));
        $this->createDocument($data);
        $data = array('name' => 'mega', 'value' => 'myTestVal' . rand(100, 10000));
        $this->createDocument($data);
        $this->refreshIndex();


        $countRequest = new CountRequest(self::INDEX, self::TYPE, $this->serializer);

        /** @var CountResponse $response */
        $response = $this->client->send($countRequest);
        $this->assertSame(3, $response->getCount());

        //create alias
        $aliasPostfix = '-alias';
        $addAliases = array(
            'actions' => array(
                array(
                    'add' => array('index' => self::INDEX, 'alias' => self::INDEX . $aliasPostfix)
                )
            )
        );

        $timeStart = microtime(true);
        $updateAliasesRequest = new UpdateAliasesRequest(null, null, $this->serializer);
        $updateAliasesRequest->setBody($addAliases);
        $this->client->send($updateAliasesRequest);

        echo 'update aliases: ' . (microtime(true) - $timeStart) . 's' . PHP_EOL;

        $getAliasesReuqest = new GetAliasesRequest(self::INDEX, null, $this->serializer);
        $getAliasResponse = $this->client->send($getAliasesReuqest);
        $aliases = $getAliasResponse->getData()->getGatewayValue();
        $this->assertTrue(isset($aliases[self::INDEX]));
        $this->assertCount(1, $aliases[self::INDEX]['aliases']);
        $this->assertTrue(isset($aliases[self::INDEX]['aliases'][self::INDEX . $aliasPostfix]));


        $removeAliases = array(
            'actions' => array(
                array(
                    'remove' => array('index' => self::INDEX, 'alias' => self::INDEX . $aliasPostfix)
                )
            )
        );

        $updateAliasesRequest->setBody($removeAliases);
        $this->client->send($updateAliasesRequest);
        $getAliasResponse = $this->client->send($getAliasesReuqest);
        $aliases = $getAliasResponse->getData()->getGatewayValue();
        $this->assertTrue(isset($aliases[self::INDEX]));
        $this->assertCount(0, $aliases[self::INDEX]['aliases']);
    }




























    private function createIndex($index = null)
    {
        if(null === $index) {
            $index = self::INDEX;
        }

        $createIndexRequest = new CreateIndexRequest($index, null, $this->serializer);
        $this->client->send($createIndexRequest);
    }

    private function hasIndex($index = null)
    {
        if(null === $index) {
            $index = self::INDEX;
        }

        $indexExistsRequest = new IndexExistsRequest($index, null, $this->serializer);
        try {
            $this->client->send($indexExistsRequest);
            return true;
        } catch(ClientException $exception) {
            return false;
        }

    }

    private function deleteIndex($index = null)
    {
        if(null === $index) {
            $index = self::INDEX;
        }

        $deleteIndexRequest = new DeleteIndexRequest($index, null, $this->serializer);
        $this->client->send($deleteIndexRequest);
    }

    private function refreshIndex($index = null)
    {
        if(null === $index) {
            $index = self::INDEX;
        }

        $refreshIndexRequest = new RefreshIndexRequest($index, null, $this->serializer);
        $this->client->send($refreshIndexRequest);
    }

    private function createDocument($data = null)
    {
        $createDocumentRequest = new CreateDocumentRequest(self::INDEX, self::TYPE, $this->serializer);
        if(null === $data) {
            $data = array('name' => 'test' . rand(100, 10000), 'value' => 'myTestVal' . rand(100, 10000));
        }

        $createDocumentRequest->setBody($data);
        /** @var CreateUpdateDocumentResponse $response */
        $response = $this->client->send($createDocumentRequest);

        return $response->getId();
    }
}
