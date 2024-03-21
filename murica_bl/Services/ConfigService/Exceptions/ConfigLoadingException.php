<?php

namespace murica_bl\Services\ConfigService\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class ConfigLoadingException extends Exception
{
    #[Pure]
    public function __construct(string $message = "", ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}