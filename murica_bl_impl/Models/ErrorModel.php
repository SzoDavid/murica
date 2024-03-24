<?php

namespace murica_bl_impl\Models;

use murica_bl\Router\IRouter;

class ErrorModel extends MessageModel {

    public function __construct(IRouter $router, int $code, string $message, string $details) {
        parent::__construct($router, ['error' => [
            'code' => $code,
            'message' => $message,
            'details' => $details]], false);
    }
}