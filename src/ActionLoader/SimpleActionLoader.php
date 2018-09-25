<?php

namespace Popo1h\PhaadminProvider\ActionLoader;

use Popo1h\PhaadminCore\Action;
use Popo1h\PhaadminProvider\ActionLoader;

class SimpleActionLoader extends ActionLoader
{
    /**
     * @var Action[]
     */
    private $actionMap;

    public function __construct()
    {
        $this->actionMap = [];
    }

    /**
     * @param Action $action
     */
    public function registerAction(Action $action)
    {
        $this->actionMap[$action::getName()] = $action;
    }

    public function load($actionName)
    {
        if (!isset($this->actionMap[$actionName])) {
            return null;
        }
        return $this->actionMap[$actionName];
    }

    public function getActionMap()
    {
        return $this->actionMap;
    }
}
