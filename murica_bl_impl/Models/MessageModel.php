<?php

namespace murica_bl_impl\Models;

use murica_bl\Router\IRouter;
use Override;

class MessageModel extends Model {

    private array $message;

    public function __construct(IRouter $router, array $message) {
        parent::__construct($router);
        $this->message = $message;
    }

    #[Override]
    public function jsonSerialize(): array {
        $links = $this->getLinks();
        if (empty($links['_links'])) return $this->message;

        return array_merge($this->message, $this->getLinks());
    }
}