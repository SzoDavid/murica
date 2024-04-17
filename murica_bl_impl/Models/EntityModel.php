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
    public function jsonSerialize(bool $root=true): array {
        $links = $this->getLinks();

        $result = $this->entity->jsonSerialize();

        if (!empty($links['_links'])) $result = array_merge($result, $links);
        if ($root) $result['_success'] = $this->success;

        return $result;
    }
}