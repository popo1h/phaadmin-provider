<?php

namespace Popo1h\PhaadminProvider\ProviderServerBuilder;

use Popo1h\PhaadminCore\Net;
use Popo1h\PhaadminProvider\Action\ActionDocInfoAction;
use Popo1h\PhaadminProvider\Action\ProviderInfoAction;
use Popo1h\PhaadminProvider\ActionAuthLoader;
use Popo1h\PhaadminProvider\ActionLoader;
use Popo1h\PhaadminProvider\ActionLoader\SimpleActionLoader;
use Popo1h\PhaadminProvider\ProviderServer\RemoteProviderServer;

class RemoteProviderServerBuilder
{
    /**
     * @param Net $net
     * @param ActionLoader[] $actionLoaders
     * @param ActionAuthLoader[] $actionAuthLoaders
     * @return RemoteProviderServer
     */
    public static function builderServer(Net $net, $actionLoaders, $actionAuthLoaders = [])
    {
        $providerServer = new RemoteProviderServer($net);

        $providerInfoAction = new ProviderInfoAction();
        $actionDocInfoAction = new ActionDocInfoAction();

        //基础loader
        $baseActionLoader = new SimpleActionLoader();
        $baseActionLoader->registerAction($providerInfoAction);
        $baseActionLoader->registerAction($actionDocInfoAction);
        $providerServer->appendActionLoader($baseActionLoader);

        //action loader
        foreach ($actionLoaders as $actionLoader) {
            if (!$actionLoader instanceof ActionLoader) {
                continue;
            }
            $providerServer->appendActionLoader($actionLoader);
        }

        //服务信息action配置
        $providerInfoAction->setActionLoaders($providerServer->getActionLoaders());
        $providerInfoAction->setActionAuthLoaders($actionAuthLoaders);
        //接口文档action配置
        $actionDocInfoAction->setActionLoaders($providerServer->getActionLoaders());

        return $providerServer;
    }
}
