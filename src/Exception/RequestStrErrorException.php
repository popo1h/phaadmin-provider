<?php

namespace Popo1h\PhaadminProvider\Exception;

use Popo1h\PhaadminCore\PhaadminException;

class RequestStrErrorException extends PhaadminException
{
    /**
     * @var string
     */
    private $requestStr;

    /**
     * RequestStrErrorException constructor.
     * @param string $requestStr
     */
    public function __construct(string $requestStr)
    {
        $this->requestStr = $requestStr;
    }

    /**
     * @return string
     */
    public function getRequestStr()
    {
        return $this->requestStr;
    }
}
