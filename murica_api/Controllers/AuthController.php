<?php

namespace murica_api\Controllers;

use murica_bl\Dao\IUserDao;
use murica_bl\Services\TokenService\ITokenService;
use murica_bl_impl\Dto\User;
use Override;

class AuthController extends Controller {
    //region Fields
    private ITokenService $tokenService;
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(string $baseUri, ITokenService $tokenService, IUserDao $userDao) {
        parent::__construct($baseUri);
        $this->tokenService = $tokenService;
        $this->userDao = $userDao;
    }
    //endregion

    //region Controller members
    #[Override]
    public function getEndpoints(): array {
        return [
            $this->baseUri . '/login' => 'login'
        ];
    }

    #[Override]
    public function getPublicEndpoints(): array {
        return [
            'login' => ''
        ];
    }
    //endregion

    //region Endpoints
    public function login(array $requestData): ?string {
        //TODO: check account validity
        if ($this->userDao->findByCrit(new User('', $requestData['username'], '', '', '')))
            return json_encode($this->tokenService->generateToken($requestData['username']));

        return json_encode('Authentication failed');
    }
    //endregion
}