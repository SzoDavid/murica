<?php

namespace murica_bl_impl\Router;

use murica_bl\Router\IEndpointRoute;
use Override;

class EndpointRoute implements IEndpointRoute {
    public const VISIBILITY_PUBLIC = false;
    public const VISIBILITY_PRIVATE = true;
    private string $endpoint;
    private bool $visibility;

    public function __construct(string $endpoint, bool $visibility) {
        $this->endpoint = $endpoint;
        $this->visibility = $visibility;
    }

    #[Override]
    public function getEndpoint(): string {
        return $this->endpoint;
    }

    #[Override]
    public function isVisible(): bool {
        return $this->visibility;
    }
}