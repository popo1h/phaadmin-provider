<?php

namespace Popo1h\PhaadminProvider;

use Popo1h\PhaadminCore\Action;
use Popo1h\PhaadminCore\Request;
use Popo1h\PhaadminProvider\Exception\ActionNotFoundException;
use Popo1h\PhaadminProvider\Exception\RequestStrErrorException;
use Popo1h\Support\Objects\StringPack;

abstract class ProviderServer
{
    /**
     * @var ActionLoader[]
     */
    protected $actionLoaders;

    public function __construct()
    {
        $this->actionLoaders = [];
    }

    /**
     * @param ActionLoader $actionLoader
     * @param bool $front append on front
     */
    public function appendActionLoader(ActionLoader $actionLoader, $front = false)
    {
        if ($front) {
            array_unshift($this->actionLoaders, $actionLoader);
        } else {
            array_push($this->actionLoaders, $actionLoader);
        }
    }

    /**
     * @return ActionLoader[]
     */
    public function getActionLoaders()
    {
        return $this->actionLoaders;
    }

    /**
     * @param string $actionName
     * @return Action|null
     */
    protected function getAction($actionName)
    {
        foreach ($this->actionLoaders as $actionLoader) {
            $action = $actionLoader->load($actionName);
            if ($action && $action instanceof Action) {
                return $action;
            }
        }

        return null;
    }

    /**
     * @param string $requestStr
     * @return \Popo1h\PhaadminCore\Response
     * @throws RequestStrErrorException
     * @throws ActionNotFoundException
     */
    protected function buildResponse($requestStr)
    {
        try {
            $request = StringPack::unpack($requestStr);
        } catch (\Exception $e) {
            throw new RequestStrErrorException($requestStr);
        }

        $actionName = $request->getServerDataByName(Request::SERVER_NAME_ACTION_NAME);
        $action = $this->getAction($actionName);
        if (!$action instanceof Action) {
            throw new ActionNotFoundException($actionName);
        }

        $action->setRequest($request);

        return $action->doAction();
    }
}
