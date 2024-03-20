<?php

namespace murica_api\Controllers;

class ErrorController extends Controller
{
    //region IController members
    #[\Override]
    public function getEndpoints(): array
    {
        return [
            $this->baseUri . '404' => 'error404',
            $this->baseUri . '418' => 'error418'
        ];
    }
    //endregion

    //region Endpoints
    public function error404($requestData): string
    {
        return json_encode("Endpoint " . $requestData["endpointName"] . " not found.");
    }

    public function error418($requestData): string
    {
        return json_encode("I'm a teapot");
    }
    //endregion
}