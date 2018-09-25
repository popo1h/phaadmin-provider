<?php

namespace Popo1h\PhaadminProvider;

use Popo1h\PhaadminCore\Action;

abstract class ActionLoader
{
    /**
     * @param string $actionName
     * @return Action|null
     */
    abstract public function load($actionName);

    /**
     * @return Action[] key is actionName
     */
    abstract public function getActionMap();
}
