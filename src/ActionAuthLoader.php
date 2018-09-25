<?php

namespace Popo1h\PhaadminProvider;

use Popo1h\PhaadminCore\ActionAuth;

abstract class ActionAuthLoader
{
    /**
     * @param string $actionAuthName
     * @return ActionAuth|null
     */
    abstract public function load($actionAuthName);

    /**
     * @return ActionAuth[] key is actionAuthName
     */
    abstract public function getActionAuthMap();
}
