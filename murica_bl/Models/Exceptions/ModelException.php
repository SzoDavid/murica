<?php

namespace murica_bl\Models\Exceptions;

use JetBrains\PhpStorm\Pure;
use murica_bl\Exceptions\MuricaException;
use Throwable;

class ModelException extends MuricaException {
    #[Pure]
    public function __construct(string $message = "", ?Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
    }
}