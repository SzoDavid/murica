<?php

namespace murica_bl\Models;

use murica_bl\Services\ConfigService\IConfigService;

class Model
{
    private IConfigService $configService;
    protected array $links;

    public function __construct(IConfigService $configService)
    {
        $this->configService = $configService;
        $links = array();
    }

    public function linkTo(string $endpoint, array $parameters, string $name): void {
        $uri = $this->configService->getHostName() . $this->configService->getBaseUri() . $endpoint;

        if (!empty($parameters)) {
            $serializedParameters = array();

            foreach ($parameters as $key => $value) {
                $serializedParameters[] = "$key=$value";
            }

            $uri .= '?' . implode('&', $serializedParameters);
        }

        $links[$name] = $uri;
    }

    public function seiralize(): string {
        return '{}';
    }

    protected function serializeLinks(): string {
        $serializedLinks = array();

        foreach ($this->links as $key => $value) {
            $serializedLinks[] = $key .
            $serializedParameters[] = '"' . $key . '":"{"href":"' . $value . '"}';
        }

        return '"_links":{' . implode(',', $serializedLinks) . '}';
    }
}