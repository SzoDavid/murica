<?php

namespace murica_bl\Models;

interface IModel {
    public function linkTo(string $name, string $class, string $method, array $uriParameters=array(), array $parameters=array()): IModel;
    public function withSelfRef(string $class, string $method, array $uriParameters=array(), array $parameters=array()): IModel;
}