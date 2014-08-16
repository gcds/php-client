<?php
/**
 * Created by PhpStorm.
 * User: dwendlandt
 * Date: 20/06/14
 * Time: 08:09
 */

namespace Elastification\Client\Request\V1x;

use Elastification\Client\Request\Shared\AbstractUpdateDocumentRequest;
use Elastification\Client\Response\V1x\CreateUpdateDocumentResponse;
use Elastification\Client\Serializer\SerializerInterface;

class UpdateDocumentRequest extends AbstractUpdateDocumentRequest
{
    /**
     * @param string $rawData
     * @param \Elastification\Client\Serializer\SerializerInterface $serializer
     * @param array $serializerParams
     * @return CreateUpdateDocumentResponse
     */
    public function createResponse(
        $rawData,
        SerializerInterface $serializer,
        array $serializerParams = array())
    {
        return new CreateUpdateDocumentResponse($rawData, $serializer, $serializerParams);
    }

    /**
     * gets a response class name that is supported by this class
     *
     * @return string
     */
    public function getSupportedClass()
    {
        return 'Elastification\Client\Response\V1x\CreateUpdateDocumentResponse';
    }
}