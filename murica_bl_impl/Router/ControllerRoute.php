<?php

namespace murica_bl_impl\Router;

use murica_api\Controllers\AuthController;
use murica_bl\Controller\IController;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\Exceptions\UriAssemblingException;
use murica_bl\Router\IControllerRoute;
use murica_bl\Router\IEndpointRoute;
use murica_bl\Router\IRouter;
use murica_bl_impl\Models\ErrorModel;
use Override;

class ControllerRoute implements IControllerRoute {
    private IRouter $router;
    private array $endpointRoutes;
    private IController $controller;

    public function __construct(IRouter $router, IController $controller) {
        $this->endpointRoutes = array();
        $this->router = $router;
        $this->controller = $controller;
    }

    #[Override]
    public function registerEndpoint(string $endpoint, string $route, bool $visibility): IControllerRoute {
        $this->endpointRoutes[$route] = new EndpointRoute($endpoint, $visibility);
        return $this;
    }

    #[Override]
    public function resolveRequest(string $uri, array $requestData): IModel {
        $uriElements = explode('/', $uri);

        /* @var $endpointRoute IEndpointRoute */
        if (!isset($this->endpointRoutes[$uriElements[0]])) {
            if (isset($this->endpointRoutes[''])) {
                $endpointRoute = $this->endpointRoutes[''];
            } else {
                return new ErrorModel($this->router, 404, 'Not found', "Endpoint `$uri` not found");
            }
        } else {
            $endpointRoute = $this->endpointRoutes[$uriElements[0]];
            array_shift($uriElements);
        }

        $endpoint = $endpointRoute->getEndpoint();

        if (!$endpointRoute->isVisible()) {
            try {
                if (!isset($requestData['token']) || !$token = $this->router->getTokenService()->verifyToken($requestData['token']))
                    return (new ErrorModel($this->router, 401, 'Unauthorized', 'Missing or invalid token'))
                        ->linkTo('login', AuthController::class, 'login');

                $requestData['token'] = $token;
            } catch (DataAccessException|ModelException $e) {
                return new ErrorModel($this->router, 500, 'Internal server error', 'Failed to verify token');
            }
        }

        return $this->controller->$endpoint($uri, $requestData);
    }

    #[Override]
    public function assembleUri(string $method, array $uriParameters, array $parameters): string {
        /* @var $endpointRoute IEndpointRoute */
        foreach ($this->endpointRoutes as $route => $endpointRoute) {
            if ($endpointRoute->getEndpoint() !== $method) continue;

            $uri = implode('/', array_merge([$route], $uriParameters));

            if (!empty($parameters)) {
                $serializedParameters = array();

                foreach ($parameters as $key => $value) {
                    $serializedParameters[] = "$key=$value";
                }

                $uri .= '?' . implode('&', $serializedParameters);
            }

            return $uri;
        }

        throw new UriAssemblingException("Could not find endpoint with method <$method>");
    }

    #[Override]
    public function getControllerType(): string {
        return get_class($this->controller);
    }
}