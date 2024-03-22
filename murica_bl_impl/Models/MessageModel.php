<?php

namespace murica_bl_impl\Models;

use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Services\ConfigService\IConfigService;
use Override;

class MessageModel extends Model {

    private array $message;

    public function __construct(IConfigService $configService) {
        parent::__construct($configService);
    }

    public function of(array $message): MessageModel {
        $this->message = $message;
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array {
        if (empty($this->message)) throw new ModelException('Message is not set');

        $links = $this->getLinks();
        if (empty($links['_links'])) return $this->message;

        return array_merge($this->message, $this->getLinks());
    }
}