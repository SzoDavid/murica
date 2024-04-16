<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IUser;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class UserController extends Controller {
    //region Properties
    private IUserDao $userDao;
    private IAdminDao $adminDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, IUserDao $userDao, IAdminDao $adminDao) {
        parent::__construct($router);
        $this->userDao = $userDao;
        $this->adminDao = $adminDao;

        $this->router->registerController($this, 'user')
            ->registerEndpoint('allUsers', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getUserById', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('createUser', 'new', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('updateUser', 'update', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('deleteUser', 'delete', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    /**
     * Returns with a collection of all users from the datasource.
     * No parameters required. User must have admin role, to access.
     */
    public function allUsers(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to query users', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query users', $e->getTraceMessages());
        }

        try {
            $users = $this->userDao->findAll();

            $userEntities = array();

            /* @var $user User */
            foreach ($users as $user) {
                $userEntities[] = (new EntityModel($this->router, $user, true))
                    ->linkTo('allUsers', UserController::class, 'allUsers')
                    ->linkTo('delete', UserController::class, 'deleteUser')
                    ->linkTo('update', UserController::class, 'updateUser')
                    ->withSelfRef(UserController::class, 'getUserById', [$user->getId()]);
            }

            return (new CollectionModel($this->router, $userEntities, 'users', true))
                ->linkTo('createUser', UserController::class, 'createUser')
                ->withSelfRef(UserController::class, 'allUsers');
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query users',
                                  $e->getTraceMessages());
        }
    }

    /**
     * Returns with the user with the given id from the datasource.
     * Id must be part of the uri. User must have admin role, to access.
     */
    public function getUserById(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to query user', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query user', $e->getTraceMessages());
        }

        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query user',
                                  'Parameter "id" is not provided in uri');
        }

        try {
            $users = $this->userDao->findByCrit(new User($uri));

            if (empty($users)) {
                return new ErrorModel($this->router,
                                      404,
                                      'User not found',
                                      "User not found with id '$uri'");
            }

            return (new EntityModel($this->router, $users[0], true))
                ->linkTo('allUsers', UserController::class, 'allUsers')
                ->linkTo('update', UserController::class, 'updateUser')
                ->withSelfRef(UserController::class, 'getUserById', [$uri]);
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query user',
                                  $e->getTraceMessages());
        }
    }

    /**
     * Returns with the user created with the given values.
     * Parameters are expected as part of request data.
     * User must have admin role, to access.
     */
    public function createUser(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to create user', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create user', $e->getTraceMessages());
        }

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
            $user = $this->userDao->create(new User($requestData['id'],
                                                    $requestData['name'],
                                                    $requestData['email'],
                                                    password_hash($requestData['password'], PASSWORD_DEFAULT),
                                                    $requestData['birth_date']));

            return (new EntityModel($this->router, $user, true))
                ->linkTo('allUsers', UserController::class, 'allUsers')
                ->withSelfRef(UserController::class, 'getUserById', [$user->getId()]);
        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create user', $e->getTraceMessages());
        }
    }

    public function updateUser(string $uri, array $requestData): IModel {
        try {
            /* @var $user IUser */
            $user = $this->$requestData['token']->getUser();

            if (!$this->checkIfAdmin($requestData, $this->adminDao) &&
                $this->userDao->findByCrit(new User($requestData['id']))[0]->getId() !== $user->getId())
                    return new ErrorModel($this->router, 403, 'Failed to update user', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update user', $e->getTraceMessages());
        }

        try {
            if (!isset($requestData['id']))
                return new ErrorModel($this->router, 400, 'Failed to update user', 'Parameter "id" is not provided in request data');

            $users = $this->userDao->findByCrit(new User($requestData['id']));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to update user', "User not found with id '{$requestData['id']}'");
            }

            /* @var $user IUser */
            $user = $users[0];

            if (isset($requestData['name'])) $user->setName($requestData['name']);
            if (isset($requestData['email'])) $user->setEmail($requestData['email']);
            if (isset($requestData['password'])) $user->setPassword(password_hash(trim($requestData['password']), PASSWORD_DEFAULT) );
            if (isset($requestData['birth_date'])) $user->setBirthDate($requestData['birth_date']);

            $this->userDao->update($user);
            return new MessageModel($this->router, ['message' => 'User updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update user', $e->getTraceMessages());
        }
    }

    public function deleteUser(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to delete user', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete user', $e->getTraceMessages());
        }

        if (!isset($requestData['id'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete user', 'Parameter "id" is not provided in request data');
        }

        try {
            $users = $this->userDao->findByCrit(new User($requestData['id']));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to delete user', "User not found with id '{$requestData['id']}'");
            }

            $this->userDao->delete($users[0]);
            return new MessageModel($this->router, ['message' => 'User deleted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete user', $e->getTraceMessages());
        }
    }
    //endregion

}