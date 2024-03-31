<?php

namespace murica_bl\Router;

interface IEndpointRoute {
    public function getEndpoint(): string;
    public function isVisible(): bool;
}