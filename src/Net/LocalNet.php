<?php

namespace Popo1h\PhaadminProvider\Net;

use Popo1h\PhaadminCore\Net;
use Popo1h\PhaadminCore\Response;
use Popo1h\PhaadminCore\Response\ErrorResponse;
use Popo1h\PhaadminProvider\ProviderServer\LocalProviderServer;
use Popo1h\Support\Objects\StringPack;

class LocalNet extends Net
{
    /**
     * @var LocalProviderServer
     */
    private $providerServer;

    /**
     * LocalNet constructor.
     * @param LocalProviderServer $providerServer
     */
    public function __construct(LocalProviderServer $providerServer)
    {
        $this->providerServer = $providerServer;
    }

    public function request($requestUrl, $requestStr, $hostIps = null)
    {
        if (!isset($this->providerServer)) {
            $response = new ErrorResponse('provider_server_not_set', [
                'request_str' => $requestStr,
            ]);
        } else {
            $response = $this->providerServer->doAction($requestStr);
        }

        if (!$response instanceof Response) {
            $response = new ErrorResponse('action_response_error', [
                'response' => $response,
            ]);
        }

        return StringPack::pack($response);
    }

    public function receive()
    {
        return null;
    }

    public function respond($responseStr)
    {
        return null;
    }
}
