<?php

namespace murica_bl\Models;

use murica_bl\Models\Exceptions\ModelException;

interface IModel {
    /**
     * @throws ModelException
     */
    public function linkTo(string $name, string $class, string $method, array $uriParameters=array(), array $parameters=array()): IModel;
    /**
     * @throws ModelException
     */
    public function withSelfRef(string $class, string $method, array $uriParameters=array(), array $parameters=array()): IModel;
}