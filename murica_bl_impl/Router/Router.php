<?php

namespace murica_bl_impl\Router;

use murica_bl\Controller\IController;
use murica_bl\Exceptions\NotImplementedException;
use murica_bl\Models\IModel;
use murica_bl\Router\Exceptions\UriAssemblingException;
use murica_bl\Router\IControllerRoute;
use murica_bl\Router\IRouter;
use murica_bl\Services\ConfigService\IConfigService;
use murica_bl\Services\TokenService\ITokenService;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use Override;

class Router implements IRouter {
    private IConfigService $configService;
    private ITokenService $tokenService;
    private array $controllerRoutes;

    public function __construct(IConfigService $configService, ITokenService $tokenService) {
        $this->configService = $configService;
        $this->tokenService = $tokenService;
        $this->controllerRoutes = array();
    }

    #[Override]
    public function registerController(IController $controller, $route): IControllerRoute {
        $controllerRoute = new ControllerRoute($this, $controller, $this->tokenService);
        $this->controllerRoutes[$route] = $controllerRoute;
        return $controllerRoute;
    }

    #[Override]
    public function resolveRequest(string $uri, array $requestData): IModel {
        $uriElements = explode('/', $uri);

        if (!isset($this->controllerRoutes[$uriElements[0]]))
            return new ErrorModel($this, 404, 'Not found', "Endpoint `$uri` not found");

        $controller = $uriElements[0];
        array_shift($uriElements);

        return $this->controllerRoutes[$controller]->resolveRequest(implode('/', $uriElements), $requestData);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function assembleUri(string $class, string $method, array $uriParameters, array $parameters): string {
        /* @var $controllerRoute ControllerRoute */
        foreach ($this->controllerRoutes as $route => $controllerRoute) {
            if ($controllerRoute->getControllerType() !== $class) continue;

            return implode('/', [
                        $this->configService->getHostName(),
                        $this->configService->getBaseUri(),
                        $route,
                        $controllerRoute->assembleUri($method, $uriParameters, $parameters)]);
        }

        throw new UriAssemblingException("Could not find controller with type <$class>");
    }
}