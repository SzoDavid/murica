<?php

namespace murica_bl_impl\Models;

use murica_bl\Router\IRouter;
use Override;

class MessageModel extends Model {

    private array $message;

    public function __construct(IRouter $router, array $message, bool $success) {
        parent::__construct($router, $success);
        $this->message = $message;
    }

    #[Override]
    public function jsonSerialize(): array {
        $links = $this->getLinks();
        if (empty($links['_links'])) return array_merge($this->message, ['_success' => $this->success]);

        return array_merge($this->message, $links, ['_success' => $this->success]);
    }
}