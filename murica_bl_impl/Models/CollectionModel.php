<?php

namespace murica_bl_impl\Models;

use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Router\IRouter;
use Override;

class CollectionModel extends Model {
    private array $collection;
    private string $name;

    /**
     * @throws ModelException
     */
    public function __construct(IRouter $router, array $collection, string $name, bool $success) {
        parent::__construct($router, $success);
        foreach ($collection as $item) {
            if (!$item instanceof EntityModel && !$item instanceof CollectionModel)
                throw new ModelException('Collection item is not EntityModel or CollectionModel: ' . $item);
        }

        $this->collection = $collection;
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(bool $root=true): array {
        $elements = array();

        /* @var $item Model */
        foreach ($this->collection as $item) {
            $elements[] = $item->jsonSerialize(false);
        }

        $result = ['_embedded' => [$this->name => $elements]];

        $links = $this->getLinks();
        if (!empty($links['_links'])) $result = array_merge($result, $links);
        if ($root) $result['_success'] = $this->success;

        return $result;
    }
}