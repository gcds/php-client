<?php
/**
 * Created by PhpStorm.
 * User: dwendlandt
 * Date: 17/06/14
 * Time: 19:02
 */
namespace Elastification\Client\Tests\Unit\Request\V2x\Index;

use Elastification\Client\Request\RequestMethods;
use Elastification\Client\Request\V2x\Index\DeleteIndexRequest;

class DeleteIndexRequestTest extends \PHPUnit_Framework_TestCase
{
    const INDEX = 'test-index';
    const RESPONSE_CLASS = 'Elastification\Client\Response\V2x\Index\IndexResponse';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var DeleteIndexRequest
     */
    private $request;

    protected function setUp()
    {
        parent::setUp();

        $this->serializer = $this->getMockBuilder('Elastification\Client\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        /** @noinspection PhpParamsInspection */
        $this->request = new DeleteIndexRequest(self::INDEX, null, $this->serializer);
    }

    protected function tearDown()
    {
        $this->serializer = null;
        $this->request = null;

        parent::tearDown();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(
            'Elastification\Client\Request\RequestInterface',
            $this->request
        );

        $this->assertInstanceOf(
            'Elastification\Client\Request\Shared\Index\AbstractDeleteIndexRequest',
            $this->request
        );

        $this->assertInstanceOf(
            'Elastification\Client\Request\V2x\Index\DeleteIndexRequest',
            $this->request
        );
    }

    public function testGetIndex()
    {
        $this->assertSame(self::INDEX, $this->request->getIndex());
    }

    public function testGetTypeWithNull()
    {
        $this->assertNull($this->request->getType());
    }

    public function testGetTypeWithParam()
    {
        $type = 'my-type';

        /** @noinspection PhpParamsInspection */
        $request = new DeleteIndexRequest(self::INDEX, $type, $this->serializer);

        $this->assertSame($type, $request->getType());
    }

    public function testGetMethod()
    {
        $this->assertSame(RequestMethods::DELETE, $this->request->getMethod());
    }

    public function testGetAction()
    {
        $this->assertNull($this->request->getAction());
    }

    public function testGetSerializer()
    {
        $this->assertSame($this->serializer, $this->request->getSerializer());
    }

    public function testGetSerializerParams()
    {
        $this->assertTrue(is_array($this->request->getSerializerParams()));
        $this->assertEmpty($this->request->getSerializerParams());
    }

    public function testSetGetBody()
    {
        $body = 'my test body';

        $this->serializer->expects($this->never())
            ->method('serialize');

        $this->request->setBody($body);

        $this->assertNull($this->request->getBody());
    }

    public function testGetSupportedClass()
    {
        $this->assertSame(self::RESPONSE_CLASS, $this->request->getSupportedClass());
    }

    public function testCreateResponse()
    {
        $rawData = 'raw data for testing';
        /** @noinspection PhpParamsInspection */
        $response = $this->request->createResponse($rawData, $this->serializer);

        $this->assertInstanceOf(self::RESPONSE_CLASS, $response);
    }

}
