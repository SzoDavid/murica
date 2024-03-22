<?php

namespace murica_bl_impl\Models;

use JsonSerializable;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Services\ConfigService\IConfigService;
use Override;

abstract class Model implements IModel, JsonSerializable {
    private IConfigService $configService;
    protected array $links;

    public function __construct(IConfigService $configService) {
        $this->configService = $configService;
        $this->links = array();
    }

    #[Override]
    public function linkTo(string $name, string $endpoint, array $parameters): IModel {
        $uri = $this->configService->getHostName() . $this->configService->getBaseUri() . $endpoint;

        if (!empty($parameters)) {
            $serializedParameters = array();

            foreach ($parameters as $key => $value) {
                $serializedParameters[] = "$key=$value";
            }

            $uri .= '?' . implode('&', $serializedParameters);
        }

        $this->links[$name] = $uri;

        return $this;
    }

    #[Override]
    public function withSelfRef(string $endpoint, array $parameters): IModel {
        $this->linkTo('self', $endpoint, $parameters);
        return $this;
    }

    /**
     * @throws ModelException
     */
    public abstract function jsonSerialize(): mixed;

    protected function getLinks(): array {
        $_links = array();

        if (isset($this->links)) {
            foreach ($this->links as $key => $value) {
                $_links[$key] = ['href' => $value];
            }
        }


        return ['_links' => $_links];
    }
}