<?php

namespace murica_api\Controllers;

use murica_bl\Dao\IUserDao;
use Override;

class BaseController extends Controller {
    //region Properties
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(string $baseUri, IUserDao $userDao) {
        parent::__construct($baseUri);
        $this->userDao = $userDao;
    }
    //endregion

    //region Controller members
    #[Override]
    public function getEndpoints(): array {
        return [
            $this->baseUri . 'sayhello' => 'greeting'
        ];
    }
    //endregion

    //region Endpoints
    public function greeting(array $requestData): string {
        if (!isset($requestData["name"])) {
            $requestData["name"] = "Misterious masked individual";
        }

        return json_encode("hello " . $requestData["name"] . "!");
    }
    //endregion


}