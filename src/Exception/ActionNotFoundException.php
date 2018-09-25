<?php

namespace Popo1h\PhaadminProvider\Exception;

use Popo1h\PhaadminCore\PhaadminException;

class ActionNotFoundException extends PhaadminException
{
    /**
     * @var string
     */
    private $actionName;

    /**
     * ActionNotFoundException constructor.
     * @param string $actionName
     */
    public function __construct($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }
}
