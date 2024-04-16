<?php

namespace murica_api\Controllers;

use murica_bl\Controller\IController;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dto\IUser;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Admin;

class Controller implements IController {
    protected IRouter $router;

    public function __construct(IRouter $router) {
        $this->router = $router;
    }

    /**
     * @throws DataAccessException
     */
    protected function checkIfAdmin($requestData, IAdminDao $adminDao): bool {
        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        $admin = $adminDao->findByCrit(new Admin($user));

        return empty($admin);
    }
}
