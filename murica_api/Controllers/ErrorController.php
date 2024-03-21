<?php

namespace murica_api\Controllers;

class ErrorController extends Controller
{
    //region IController members
    #[\Override]
    public function getEndpoints(): array
    {
        return [
            $this->baseUri . '401' => 'unauthorized',
            $this->baseUri . '403' => 'forbidden',
            $this->baseUri . '404' => 'notFound',
            $this->baseUri . '500' => 'internalServerError'
        ];
    }

    #[\Override] public function getPublicEndpoints(): array
    {
        return [
            'unauthorized' => '',
            'forbidden' => '',
            'notFound' => '',
            'internalServerError' => ''
        ];
    }
    //endregion

    //region Endpoints
    public function unauthorized($requestData): string
    {
        return json_encode([
            'error' => [
                'code' => 401,
                'message' => 'Client request has not been completed because it lacks valid authentication credentials for the requested resource.']]);
    }

    public function forbidden($requestData): string
    {
        return json_encode([
            'error' => [
                'code' => 403,
                'message' => 'Client request has not been completed because client has no rights to access the requested resource.']]);
    }

    public function notFound($requestData): string
    {
        return json_encode([
            'error' => [
                'code' => 404,
                'message' => 'Endpoint ' . $requestData['endpointName'] . ' not found.']]);
    }

    public function internalServerError($requestData): string
    {
        return json_encode([
            'error' => [
                'code' => 500,
                'message' => 'Internal server error: ' . $requestData['errorMessage']]]);
    }
    //endregion
}