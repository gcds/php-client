<?php

namespace Elastification\Client\Tests\Unit\Request\V1x\Cat;

use Elastification\Client\Request\RequestMethods;
use Elastification\Client\Request\Shared\Cat\AbstractHealthCatRequest;
use Elastification\Client\Request\V1x\Cat\HealthCatRequest;

class HealthCatRequestTest extends \PHPUnit_Framework_TestCase
{
    const RESPONSE_CLASS = 'Elastification\Client\Response\Response';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var HealthCatRequest
     */
    private $request;

    protected function setUp()
    {
        parent::setUp();

        $this->serializer = $this->getMockBuilder('Elastification\Client\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new HealthCatRequest(null, null, $this->serializer);
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
            'Elastification\Client\Request\Shared\Cat\AbstractHealthCatRequest',
            $this->request
        );

        $this->assertInstanceOf(
            'Elastification\Client\Request\V1x\Cat\HealthCatRequest',
            $this->request
        );
    }

    public function testGetIndex()
    {
        $this->assertSame(AbstractHealthCatRequest::REQUEST_ACTION, $this->request->getIndex());
    }

    public function testGetType()
    {
        $this->assertNull($this->request->getType());
    }

    public function testGetMethod()
    {
        $this->assertSame(RequestMethods::GET, $this->request->getMethod());
    }

    public function testGetAction()
    {
        $this->assertSame(AbstractHealthCatRequest::CAT_TYPE, $this->request->getAction());
    }
//
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
        $response = $this->request->createResponse($rawData, $this->serializer);

        $this->assertInstanceOf(self::RESPONSE_CLASS, $response);
    }

    public function testGetParameters()
    {
        $parameters = $this->request->getParameters();

        $this->assertArrayHasKey('format', $parameters);
        $this->assertSame('json', $parameters['format']);
    }
}
