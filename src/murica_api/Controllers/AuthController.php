<?php

namespace murica_api\Controllers;

class AuthController extends Controller
{
    //region Controller members
    #[\Override]
    public function getEndpoints(): array
    {
        return [
            $this->baseUri . 'checktoken' => 'checkToken'
        ];
    }
    //endregion

    //region Endpoints
    public function checkToken(array $requestData): ?string
    {
        $tokens = array(
            "fa3b2c9c-a96d-48a8-82ad-0cb775dd3e5d" => ""
        );

        if (!isset($requestData["token"])) {
            return json_encode("No token was received to authorize the operation. Verify the information sent");
        }

        if (!isset($tokens[$requestData["token"]])) {
            return json_encode("The token " . $requestData["token"] . " does not exists or is not authorized to perform this operation.");
        }

        return null;
    }
    //endregion
}