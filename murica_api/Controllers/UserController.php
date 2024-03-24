<?php

namespace murica_api\Controllers;

use murica_api\Exceptions\ControllerException;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IUserDao;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\QueryDto\QueryUser;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class UserController extends Controller {
    //region Properties
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, IUserDao $userDao) {
        parent::__construct($router);
        $this->userDao = $userDao;

        $this->router->registerController($this, 'user')
            ->registerEndpoint('allUsers', 'all', EndpointRoute::VISIBILITY_PUBLIC) // TODO: Set to private
            ->registerEndpoint('getUserById', '', EndpointRoute::VISIBILITY_PUBLIC) // TODO: Set to private
            ->registerEndpoint('createUser', 'new', EndpointRoute::VISIBILITY_PUBLIC); // TODO: Set to private
    }
    //endregion

    //region Endpoints
    /**
     * Returns with a collection of all users from the datasource.
     * No parameters required. User must have admin role, to access.
     */
    public function allUsers(string $uri, array $requestData): IModel {
        try {
            $users = $this->userDao->findAll();
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query users',
                                  $e->getTraceMessages());
        }

        $userEntities = array();

        /* @var $user User */
        foreach ($users as $user) {
            $userEntities[] = (new EntityModel($this->router, $user, true))
                ->linkTo('allUsers', UserController::class, 'allUsers')
                ->withSelfRef(UserController::class, 'getUserById', [$user->getId()]);
        }

        try {
            return (new CollectionModel($this->router, $userEntities, 'users', true))
                ->withSelfRef(UserController::class, 'allUsers');
        } catch (ModelException $e) {
            return new MessageModel($this->router, ['error' => [
                'code' => '500',
                'message' => 'Failed to query users',
                'details' => $e->getTraceMessages()
            ]], false);
        }
    }

    /**
     * Returns with the user with the given id from the datasource.
     * Id must be part of the uri. User must have admin role, to access.
     */
    public function getUserById(string $uri, array $requestData): IModel {
        // TODO: validate $uri as user id
        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query user',
                                  'Parameter "id" is not provided in uri');
        }

        try {
            $users = $this->userDao->findByCrit(new QueryUser($uri, null, null, null, null));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query user',
                                  $e->getTraceMessages());
        }

        if (empty($users)) {
            return new ErrorModel($this->router,
                                  404,
                                  'User not found',
                                  "User not found with id '$uri'");
        }

        return (new EntityModel($this->router, $users[0], true))
            ->linkTo('allUsers', UserController::class, 'allUsers')
            ->withSelfRef(UserController::class, 'getUserById', [$uri]);
    }

    /**
     * Returns with the user created with the given values.
     * Parameters are expected as part of request data.
     * User must have admin role, to access.
     */
    public function createUser(string $uri, array $requestData): IModel {
        //TODO: make private
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to create user', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['name']))
            return new ErrorModel($this->router, 400, 'Failed to create user', 'Parameter "name" is not provided in uri');
        if (!isset($requestData['email']))
            return new ErrorModel($this->router, 400, 'Failed to create user', 'Parameter "email" is not provided in uri');
        if (!isset($requestData['password']))
            return new ErrorModel($this->router, 400, 'Failed to create user', 'Parameter "password" is not provided in uri');
        if (!isset($requestData['birth_date']))
            return new ErrorModel($this->router, 400, 'Failed to create user', 'Parameter "birth_date" is not provided in uri');

        try {
            $user = $this->userDao->insert(new User($requestData['id'],
                                                    $requestData['name'],
                                                    $requestData['email'],
                                                    password_hash($requestData['password'], PASSWORD_DEFAULT),
                                                    $requestData['birth_date']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to create user',
                                  $e->getTraceMessages());
        }

        return (new EntityModel($this->router, $user, true))
            ->linkTo('allUsers', UserController::class, 'allUsers')
            ->withSelfRef(UserController::class, 'getUserById', [$user->getId()]);
    }
    //endregion

}