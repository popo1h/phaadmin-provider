<?php

namespace Popo1h\PhaadminProvider;

use Popo1h\PhaadminCore\Response;

abstract class ResponseHandler
{
    /**
     * @param Response $response
     * @return mixed
     */
    abstract public function handler(Response $response);
}