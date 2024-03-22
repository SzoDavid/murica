<?php

namespace murica_bl\Models;

interface IModel
{
    public function linkTo(string $name, string $endpoint, array $parameters): IModel;
    public function withSelfRef(string $endpoint, array $parameters): IModel;
}