## Remote Server ##

    $actionLoader = new \Popo1h\PhaadminProvider\ActionLoader\PathActionLoader(
        'action_path',
        'action_base_namespace'
    );
    $server = \Popo1h\PhaadminProvider\ProviderServerBuilder\RemoteProviderServerBuilder::buildServer(
        new \Popo1h\PhaadminCore\Net\HttpNet(),
        [$actionLoader],
        []
    );