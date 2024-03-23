<?php

namespace murica_api\Controllers;

use murica_api\Exceptions\ControllerException;
use murica_api\Exceptions\QueryException;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IUserDao;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\QueryDto\QueryUser;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Router\EndpointRoute;

class UserController extends Controller {
    //region Properties
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, IUserDao $userDao) {
        parent::__construct($router);
        $this->userDao = $userDao;

        $this->getRouter()->registerController($this, 'user')
            ->registerEndpoint('allUsers', 'all', EndpointRoute::VISIBILITY_PUBLIC) // TODO: Set to private
            ->registerEndpoint('getUserById', '', EndpointRoute::VISIBILITY_PUBLIC) // TODO: Set to private
            ->registerEndpoint('createUser', 'new', EndpointRoute::VISIBILITY_PUBLIC); // TODO: Set to private
    }
    //endregion

    //region Endpoints
    /**
     * @throws ControllerException
     * @throws DataAccessException
     */
    public function allUsers(string $uri, array $requestData): IModel {
        $users = $this->userDao->findAll();

        $userEntities = array();

        /* @var $user User */
        foreach ($users as $user) {
            $userEntities[] = (new EntityModel($this->getRouter(), $user))
                ->linkTo('allUsers', UserController::class, 'allUsers')
                ->withSelfRef(UserController::class, 'getUserById', [$user->getId()]);
        }

        try {
            return (new CollectionModel($this->configService))->of($userEntities, 'users')->withSelfRef($this->baseUri, array());
        } catch (ModelException $e) {
            throw new ControllerException('Failed to serialize result', $e);
        }
    }

    /**
     * @throws ControllerException
     * @throws QueryException
     * @throws DataAccessException
     */
    public function getUserById(string $uri, array $requestData): IModel {
        if (!isset($requestData['id'])) throw new ControllerException('Parameter "id" is not provided');

        $users = $this->userDao->findByCrit(new QueryUser($requestData['id'], null, null, null, null));

        if (empty($users)) throw new QueryException('Failed to get user with id "' . $requestData['id'] . '"');

        return (new EntityModel($this->configService))->of($users[0])
            ->linkTo('allUsers', $this->baseUri)
            ->withSelfRef($this->baseUri . '/user', ['id' => $users[0]->getId()]);
    }

    /**
     * @throws ControllerException
     * @throws DataAccessException
     */
    public function createUser(string $uri, array $requestData): IModel {
        //TODO: make private
        if (!isset($requestData['id'])) throw new ControllerException('Parameter "id" is not provided');
        if (!isset($requestData['name'])) throw new ControllerException('Parameter "name" is not provided');
        if (!isset($requestData['email'])) throw new ControllerException('Parameter "email" is not provided');
        if (!isset($requestData['password'])) throw new ControllerException('Parameter "password" is not provided');
        if (!isset($requestData['birth_date'])) throw new ControllerException('Parameter "birth_date" is not provided');

        $user = $this->userDao->insert(new User($requestData['id'],
                                                $requestData['name'],
                                                $requestData['email'],
                                                password_hash($requestData['password'], PASSWORD_DEFAULT),
                                                $requestData['birth_date']));

        return (new EntityModel($this->configService))->of($user)
            ->linkTo('allUsers', $this->baseUri)
            ->withSelfRef($this->baseUri . '/user', ['id' => $user->getId()]);
    }
    //endregion

}