<?php

namespace murica_api\Controllers;

use Override;

class ErrorController extends Controller
{
    //region IController members
    #[Override]
    public function getEndpoints(): array {
        return [
            $this->baseUri . '/401' => 'unauthorized',
            $this->baseUri . '/403' => 'forbidden',
            $this->baseUri . '/404' => 'notFound',
            $this->baseUri . '/500' => 'internalServerError'
        ];
    }

    #[Override]
    public function getPublicEndpoints(): array {
        return [
            'unauthorized' => '',
            'forbidden' => '',
            'notFound' => '',
            'internalServerError' => ''
        ];
    }
    //endregion

    //region Endpoints
    public function unauthorized($requestData): array {
        return ['error' => [
                    'code' => 401,
                    'message' => 'Client request has not been completed because it lacks valid authentication credentials for the requested resource.']];
    }

    public function forbidden($requestData): array {
        return ['error' => [
                    'code' => 403,
                    'message' => 'Client request has not been completed because client has no rights to access the requested resource.']];
    }

    public function notFound($requestData): array {
        $message = 'Requested resource not found';
        if (isset($requestData['endpoint'])) {
            $message = 'Endpoint ' . $requestData['endpoint'] . ' not found.';
        } elseif (isset($requestData['resource'])) {
            $message = $requestData['resource'];
        }

        return ['error' => [
                    'code' => 404,
                    'message' => $message]];
    }

    public function internalServerError($requestData): array {
        $message = 'Internal server error';
        if (isset($requestData['errorMessage'])) {
            $message .= ': ' . $requestData['errorMessage'];
        }

        return ['error' => [
                    'code' => 500,
                    'message' => $message]];
    }
    //endregion
}