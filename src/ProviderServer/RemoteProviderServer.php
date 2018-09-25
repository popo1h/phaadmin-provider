<?php

namespace Popo1h\PhaadminProvider\ProviderServer;

use Popo1h\PhaadminCore\Net;
use Popo1h\PhaadminCore\Response;
use Popo1h\PhaadminCore\Response\ErrorResponse;
use Popo1h\PhaadminProvider\Exception\ActionNotFoundException;
use Popo1h\PhaadminProvider\Exception\RequestStrErrorException;
use Popo1h\PhaadminProvider\ProviderServer;
use Popo1h\PhaadminProvider\ResponseHandler;
use Popo1h\PhaadminProvider\ResponseHandler\CommonResponseHandler;
use Popo1h\Support\Objects\StringPack;

class RemoteProviderServer extends ProviderServer
{
    /**
     * @var Net
     */
    protected $net;
    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * RemoteProviderServer constructor.
     * @param Net $net
     */
    public function __construct(Net $net)
    {
        parent::__construct();
        $this->net = $net;
    }

    /**
     * @param ResponseHandler $responseHandler
     */
    public function setResponseHandler(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * @return ResponseHandler
     */
    private function getResponseHandler()
    {
        if (!isset($this->responseHandler)) {
            $this->responseHandler = new CommonResponseHandler();
        }

        return $this->responseHandler;
    }

    public function listen()
    {
        $requestStr = $this->net->receive();

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

        $response = $this->net->respond(StringPack::pack($actionResponse, true));

        return $this->getResponseHandler()->handler($response);
    }
}
