<?php

namespace Popo1h\PhaadminProvider\ProviderServer;

use Popo1h\PhaadminCore\Response;
use Popo1h\PhaadminCore\Response\ErrorResponse;
use Popo1h\PhaadminProvider\Exception\ActionNotFoundException;
use Popo1h\PhaadminProvider\Exception\RequestStrErrorException;
use Popo1h\PhaadminProvider\ProviderServer;

class LocalProviderServer extends ProviderServer
{
    /**
     * @param string $requestStr
     * @return Response
     */
    public function doAction($requestStr)
    {
        try {
            $actionResponse = $this->buildResponse($requestStr);

            if (!$actionResponse instanceof Response) {
                $actionResponse = new ErrorResponse('action respond error');
            }
        } catch (RequestStrErrorException $requestStrErrorException) {
            $actionResponse = new ErrorResponse('request_str_error', $requestStrErrorException->getRequestStr());
        } catch (ActionNotFoundException $actionNotFoundException) {
            $actionResponse = new ErrorResponse('action_not_found', $actionNotFoundException->getActionName());
        } catch (\Exception $e) {
            $actionResponse = new ErrorResponse('build_response_error', [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'previous' => $e->getPrevious(),
            ]);
        }

        return $actionResponse;
    }
}
