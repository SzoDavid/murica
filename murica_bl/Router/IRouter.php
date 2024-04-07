<?php

namespace murica_bl\Router;

use murica_bl\Controller\IController;
use murica_bl\Models\IModel;
use murica_bl\Router\Exceptions\UriAssemblingException;
use murica_bl\Services\ConfigService\IConfigService;
use murica_bl\Services\TokenService\ITokenService;

interface IRouter {
    public function registerController(IController $controller, $route): IControllerRoute;
    public function resolveRequest(string $uri, array $requestData): IModel;
    /**
     * @throws UriAssemblingException
     */
    public function assembleUri(string $class, string $method, array $uriParameters, array $parameters): string;
}