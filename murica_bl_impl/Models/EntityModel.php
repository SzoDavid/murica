<?php

namespace murica_bl_impl\Models;

use murica_bl_impl\Router\Router;
use Override;

class EntityModel extends Model {
    private Entity $entity;

    public function __construct(Router $router, Entity $entity, bool $success) {
        parent::__construct($router, $success);
        $this->entity = $entity;
    }

    #[Override]
    public function jsonSerialize(): array {
        $links = $this->getLinks();
        if(empty($links['_links'])) return $this->entity->jsonSerialize();

        return array_merge($this->entity->jsonSerialize(), $this->getLinks(), ['_success' => $this->success]);
    }
}