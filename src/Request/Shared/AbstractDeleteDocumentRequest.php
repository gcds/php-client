<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Elastification\Client\Request\Shared;

use Elastification\Client\Exception\RequestException;
use Elastification\Client\Request\RequestMethods;

/**
 * Class AbstractDeleteDocumentRequest
 *
 * @package Elastification\Client\Request\Shared
 * @author  Daniel Wendlandt
 */
abstract class AbstractDeleteDocumentRequest extends AbstractBaseRequest
{
    /**
     * @var null|string
     */
    private $action = null;

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return RequestMethods::DELETE;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        if (null === $this->action) {
            throw new RequestException('id can not be empty for this request');
        }

        return $this->action;
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function setBody($body)
    {
        //do nothing
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        if (empty($id)) {
            throw new RequestException('Id can not be empty');
        }

        $this->action = $id;
    }
}
