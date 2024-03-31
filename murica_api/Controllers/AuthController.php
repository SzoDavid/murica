<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\IToken;
use murica_bl\Dto\IUser;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl\Services\TokenService\ITokenService;
use murica_bl_impl\Dto\QueryDto\QueryUser;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class AuthController extends Controller {
    //region Fields
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router ,IUserDao $userDao) {
        parent::__construct($router);
        $this->userDao = $userDao;

        $this->router->registerController($this, 'auth')
            ->registerEndpoint('login', 'login', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('logout', 'logout', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    /**
     * Returns with a token model after authenticating with the given id and password.
     * Parameters are excepted as part of request.
     */
    public function login(string $uri, array $requestData): IModel {
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to authenticate', 'Parameter "id" is not provided in request');
        if (!isset($requestData['password']))
            return new ErrorModel($this->router, 400, 'Failed to authenticate', 'Parameter "password" is not provided in request');

        try {
            $users = $this->userDao->findByCrit(new QueryUser($requestData['id'], null, null, null, null));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to authenticate',
                                  $e->getTraceMessages());
        }

        if (empty($users))
            return new ErrorModel($this->router, 401, 'Authentication failed', 'Invalid id or password');

        /* @var $user IUser */
        $user = $users[0];

        if (password_verify($requestData['password'], $user->getPassword())) {
            try {
                return (new EntityModel($this->router, $this->router->getTokenService()->generateToken($requestData['id']), true))
                    ->linkTo('logout', AuthController::class, 'logout');
            } catch (DataAccessException|ModelException $e) {
                return new ErrorModel($this->router, 500, 'Authentication failed', $e->getTraceMessages());
            }
        }

        return new ErrorModel($this->router, 401, 'Authentication failed', 'Invalid id or password');
    }

    /**
     * Logs user out, by removing access token.
     * Token is expected as part of request.
     * Only logged in users can access.
     */
    public function logout(string $uri, array $requestData): IModel {
        if (!isset($requestData['token']))
            return new ErrorModel($this->router, 400, 'Failed to log out', 'Parameter "token" is not provided in request');

        /* @var $token IToken */
        $token = $requestData['token'];

        try {
            $this->router->getTokenService()->removeToken($token->getToken());
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to log out', $e->getTraceMessages());
        }

        return (new MessageModel($this->router, ['message' => 'Logged out successfully'], true));
    }
    //endregion
}