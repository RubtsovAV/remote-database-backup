<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server;

use RubtsovAV\RemoteDatabaseBackup\Server\DatabaseAdapterFactory;
use RubtsovAV\RemoteDatabaseBackup\Server\Exception\Router\RouterException;
use RubtsovAV\RemoteDatabaseBackup\Server\Exception\Router\NotFoundException;
use Exception;

class Router
{
    public function route($requestData)
    {
        header('Content-Type: text/plain;charset=UTF-8');
        try {
            $adapter = $this->createAdapter($requestData);
            $action = $this->parseAction($requestData);

            if (!method_exists($adapter, $action['name'])) {
                throw new NotFoundException();
            }
            $response = call_user_func_array([$adapter, $action['name']], $action['data']);
        } catch (RouterException $ex) {
            $responseCode = $ex->getCode();
            $responseMessage = $ex->getMessage();
            header("HTTP/1.1 $responseCode $responseMessage");
        } catch (Exception $ex) {
            header('HTTP/1.1 417 Expectation failed');
            $response = [
                'error' => [
                    'message' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                    'trace' => $ex->getTrace(),
                ]
            ];
        }

        if (is_array($response)) {
            header('Content-Type: application/json');
            $response = json_encode($response);
        }

        if (isset($requestData['response_mark'])) {
            $response .= $requestData['response_mark'];
        }
        return $response;
    }

    public function createAdapter($requestData)
    {
        $adapterName = null;
        if (isset($requestData['adapter']) && is_string($requestData['adapter'])) {
            $adapterName = $requestData['adapter'];
        }

        $adapterParams = [];
        if (isset($requestData['db']) && is_array($requestData['db'])) {
            $adapterParams = $requestData['db'];
        }

        $adapterFactory = new DatabaseAdapterFactory();
        return $adapterFactory->createAdapter($adapterName, $adapterParams);
    }

    public function parseAction($requestData)
    {
        $actionName = null;
        if (isset($requestData['action']) && is_string($requestData['action'])) {
            $actionName = $requestData['action'];
        }

        $actionData = [];
        if (isset($requestData['data']) && is_array($requestData['data'])) {
            $actionData = $requestData['data'];
        }

        return [
            'name' => $actionName,
            'data' => $actionData,
        ];
    }
}
