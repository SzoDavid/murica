<?php

namespace murica_bl_impl\Models;

use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Services\ConfigService\IConfigService;
use Override;

class CollectionModel extends Model {
    private array $collection;
    private string $name;

    public function __construct(IConfigService $configService) {
        parent::__construct($configService);
    }

    /**
     * @throws ModelException
     */
    public function of(array $collection, string $name): CollectionModel {
        foreach ($collection as $item) {
            if (!$item instanceof EntityModel && !$item instanceof CollectionModel)
                throw new ModelException('Collection item is not EntityModel or CollectionModel: ' . $item);
        }

        $this->collection = $collection;
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array {
        if (!isset($this->collection)) throw new ModelException('Collection is not set');
        if (!isset($this->name)) throw new ModelException('Name is not set');

        $elements = array();

        /* @var $item Model */
        foreach ($this->collection as $item) {
            $elements[] = $item->jsonSerialize();
        }

        $links = $this->getLinks();
        if(empty(empty($links['_links']))) return ['_embedded' => [$this->name => $elements]];

        return array_merge(['_embedded' => [$this->name => $elements]], $this->getLinks());
    }
}