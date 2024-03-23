<?php

namespace murica_api\Controllers;

use murica_bl\Controller\IController;
use murica_bl\Router\IRouter;
use Override;

class Controller implements IController {
    protected IRouter $router;

    public function __construct(IRouter $router) {
        $this->router = $router;
    }

    #[Override]
    public function getRouter(): IRouter {
        return $this->router;
    }
}
