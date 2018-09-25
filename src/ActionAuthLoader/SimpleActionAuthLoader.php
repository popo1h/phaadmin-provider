<?php

namespace Popo1h\PhaadminProvider\ActionAuthLoader;

use Popo1h\PhaadminCore\ActionAuth;
use Popo1h\PhaadminProvider\ActionAuthLoader;

class SimpleActionAuthLoader extends ActionAuthLoader
{
    /**
     * @var ActionAuth[]
     */
    private $actionAuthMap;

    public function __construct()
    {
        $this->actionAuthMap = [];
    }

    /**
     * @param ActionAuth $actionAuth
     */
    public function registerActionAuth(ActionAuth $actionAuth)
    {
        $this->actionAuthMap[$actionAuth::getName()] = $actionAuth;
    }

    public function load($actionAuthName)
    {
        if (!isset($this->actionAuthMap[$actionAuthName])) {
            return null;
        }
        return $this->actionAuthMap[$actionAuthName];
    }

    public function getActionAuthMap()
    {
        return $this->actionAuthMap;
    }
}
