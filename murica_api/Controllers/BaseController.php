<?php

namespace murica_api\Controllers;

class BaseController extends Controller
{
    //region Controller members
    #[\Override]
    public function getEndpoints(): array
    {
        return [
            $this->baseUri . '/' => 'welcome',
            $this->baseUri . 'sayhello' => 'greeting'
        ];
    }

    #[\Override] public function getPublicEndpoints(): array
    {
        return [
            'welcome' => ''
        ];
    }
    //endregion

    //region Endpoints
    public function welcome(array $requestData): string
    {
        return json_encode("Welcome");
    }

    public function greeting(array $requestData): string
    {
        if (!isset($requestData["name"])) {
            $requestData["name"] = "Misterious masked individual";
        }

        return json_encode("hello " . $requestData["name"] . "!");
    }
    //endregion


}