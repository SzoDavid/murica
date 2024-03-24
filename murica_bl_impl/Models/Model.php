<?php

namespace murica_bl_impl\Models;

use JsonSerializable;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\Exceptions\UriAssemblingException;
use murica_bl\Router\IRouter;
use Override;

abstract class Model implements IModel, JsonSerializable {
    private IRouter $router;
    protected bool $success;
    protected array $links;

    public function __construct(IRouter $router, $success) {
        $this->router = $router;
        $this->success = $success;
        $this->links = array();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function linkTo(string $name, string $class, string $method, array $uriParameters=array(), array $parameters=array()): IModel {
        try {
            $this->links[$name] = $this->router->assembleUri($class, $method, $uriParameters, $parameters);
        } catch (UriAssemblingException $e) {
            throw new ModelException('Failed to link endpoint', $e);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
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