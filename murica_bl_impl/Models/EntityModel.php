<?php

namespace murica_bl_impl\Models;

use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Services\ConfigService\IConfigService;
use Override;

class EntityModel extends Model {
    private Entity $entity;

    public function __construct(IConfigService $configService) {
        parent::__construct($configService);
    }

    public function of(Entity $entity): EntityModel {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array {
        if (!isset($this->entity)) throw new ModelException('Entity is not set');

        return array_merge($this->entity->jsonSerialize(), $this->getLinks());
    }
}