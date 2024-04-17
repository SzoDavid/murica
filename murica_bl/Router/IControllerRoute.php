<?php

namespace murica_bl\Router;

use murica_bl\Models\IModel;
use murica_bl\Router\Exceptions\UriAssemblingException;

interface IControllerRoute {
    public function registerEndpoint(string $endpoint, string $route, bool $visibility): IControllerRoute;
    public function resolveRequest(string $uri, array $requestData): IModel;
    /**
     * @throws UriAssemblingException
     */
    public function assembleUri(string $method, array $uriParameters, array $parameters): string;
    public function getControllerType(): string;
}