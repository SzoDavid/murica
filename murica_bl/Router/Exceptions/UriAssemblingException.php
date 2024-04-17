<?php

namespace murica_bl\Router\Exceptions;

use JetBrains\PhpStorm\Pure;
use murica_bl\Exceptions\MuricaException;
use Throwable;

class UriAssemblingException extends MuricaException {
    #[Pure]
    public function __construct(string $message = "", ?Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
    }
}