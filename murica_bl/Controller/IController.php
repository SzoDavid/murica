<?php

namespace murica_bl\Controller;

use murica_bl\Router\IRouter;

interface IController {
    public function getRouter(): IRouter;
}