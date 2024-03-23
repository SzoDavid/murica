<?php

namespace murica_api\Controllers;

use murica_api\Exceptions\ControllerException;
use murica_api\Exceptions\QueryException;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\IToken;
use murica_bl\Dto\IUser;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl\Services\TokenService\ITokenService;
use murica_bl_impl\Dto\QueryDto\QueryUser;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class AuthController extends Controller {
    //region Fields
    private ITokenService $tokenService;
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router ,IUserDao $userDao, ITokenService $tokenService) {
        parent::__construct($router);
        $this->userDao = $userDao;
        $this->tokenService = $tokenService;

        $this->getRouter()->registerController($this, 'user')
            ->registerEndpoint('login', 'login', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('logout', 'logout', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    /**
     * @throws ControllerException
     * @throws QueryException
     * @throws DataAccessException
     */
    public function login(string $uri, array $requestData): IModel {
        if (!isset($requestData['id'])) throw new ControllerException('Parameter "id" is not provided');
        if (!isset($requestData['password'])) throw new ControllerException('Parameter "password" is not provided');

        $users = $this->userDao->findByCrit(new QueryUser($requestData['id'], null, null, null, null));

        if (empty($users)) throw new QueryException('Failed to get user with id "' . $requestData['id'] . '"');
        /* @var $user IUser */
        $user = $users[0];

        if (password_verify($requestData['password'], $user->getPassword()))
            return (new EntityModel($this->configService))
                ->of($this->tokenService->generateToken($requestData['id']))
                ->linkTo('logout', implode('/', [$this->baseUri, 'logout']));

        return (new MessageModel($this->configService))
            ->of(['error' => [
                'code' => 401,
                'message' => 'Authentication failed']]);
    }

    /**
     * @throws ControllerException
     */
    public function logout(string $uri, array $requestData): IModel {
        if (!isset($requestData['token'])) throw new ControllerException('Parameter "token" is not provided');

        /* @var $token IToken */
        $token = $requestData['token'];

        try {
            $this->tokenService->removeToken($token->getToken());
        } catch (DataAccessException $ex) {
            return (new MessageModel($this->configService))
                ->of(['error' => [
                    'code' => 500,
                    'message' => 'An unexpected error happened'
                ]]);
        }

        return (new MessageModel($this->configService))
            ->of(['success' => [
                'message' => 'Logged out successfully']]);
    }
    //endregion
}