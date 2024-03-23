<?php

namespace murica_bl_impl\Models;

use JsonSerializable;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use Override;

abstract class Model implements IModel, JsonSerializable {
    private IRouter $router;
    protected array $links;

    public function __construct(IRouter $router) {
        $this->router = $router;
        $this->links = array();
    }

    #[Override]
    public function linkTo(string $name, string $class, string $method, array $uriParameters=array(), array $parameters=array()): IModel {
        // TODO: handle exception
        $this->links[$name] = $this->router->assembleUri($class, $method, $uriParameters, $parameters);

        return $this;
    }

    #[Override]
    public function withSelfRef(string $class, string $method, array $uriParameters=array(), array $parameters=array()): IModel {
        $this->linkTo('self', $class, $method, $uriParameters, $parameters);
        return $this;
    }

    protected function getLinks(): array {
        $_links = array();

        if (isset($this->links)) {
            foreach ($this->links as $key => $value) {
                $_links[$key] = ['href' => $value];
            }
        }

        return ['_links' => $_links];
    }
}