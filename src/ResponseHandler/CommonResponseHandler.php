<?php

namespace Popo1h\PhaadminProvider\ResponseHandler;

use Popo1h\PhaadminCore\Response;
use Popo1h\PhaadminProvider\ResponseHandler;

class CommonResponseHandler extends ResponseHandler
{
    public function handler(Response $response)
    {
        $responseOutput = $response->output();

        $headers = $responseOutput->getHeaders();
        foreach ($headers as $header) {
            header($header);
        }

        http_response_code($responseOutput->getStatusCode());

        echo $responseOutput->getContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
