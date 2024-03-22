<?php

namespace murica_api\Controllers;

use murica_api\Exceptions\ControllerException;
use murica_api\Exceptions\QueryException;
use murica_bl\Dao\IUserDao;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Services\ConfigService\IConfigService;
use murica_bl_impl\Dto\QueryDto\QueryUser;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use Override;

class UserController extends Controller {
    //region Properties
    private IUserDao $userDao;
    private IConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(string $baseUri, IUserDao $userDao, IConfigService $configService) {
        parent::__construct($baseUri);
        $this->userDao = $userDao;
        $this->configService = $configService;
    }
    //endregion

    //region Controller members
    #[Override]
    public function getEndpoints(): array {
        return [
            $this->baseUri . '' => 'allUsers',
            $this->baseUri . '/user' => 'getUserById'
        ];
    }

    #[Override]
    public function getPublicEndpoints(): array {
        return [
            'allUsers' => '',
            'getUserById' => ''
        ];
    }
    //endregion

    //region Endpoints
    /**
     * @throws ControllerException
     */
    public function allUsers(array $requestData): IModel {
        $users = $this->userDao->findAll();

        $userEntities = array();

        /* @var $user User */
        foreach ($users as $user) {
            $userEntities[] = (new EntityModel($this->configService))->of($user)
                ->linkTo('allUsers', $this->baseUri, array())
                ->withSelfRef($this->baseUri . '/user', ['id' => $user->getId()]);
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
     */
    public function getUserById(array $requestData): IModel {
        if (!isset($requestData['id'])) throw new ControllerException('Parameter "id" is not provided');

        $users = $this->userDao->findByCrit(new QueryUser($requestData['id'], null, null, null, null));

        if (empty($users)) throw new QueryException('Failed to get user with id "' . $requestData['id'] . '"');

        return (new EntityModel($this->configService))->of($users[0])
            ->linkTo('allUsers', $this->baseUri, array())
            ->withSelfRef($this->baseUri . '/user', ['id' => $users[0]->getId()]);
    }
    //endregion

}