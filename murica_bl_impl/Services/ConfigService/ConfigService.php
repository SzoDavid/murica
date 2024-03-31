<?php

namespace murica_bl_impl\Services\ConfigService;

use Exception;
use murica_bl\Services\ConfigService\Exceptions\ConfigLoadingException;
use murica_bl\Services\ConfigService\IConfigService;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use Override;

class ConfigService implements IConfigService {
    //region Properties
    private IDataSourceConfigService $dataSourceConfigService;
    private string $hostName;
    private string $baseUri;
    private int $displayError;
    //endregion

    /**
     * @throws ConfigLoadingException
     */
    public function __construct(string $configPath) {
        try {
            $raw_configs = json_decode(file_get_contents($configPath), true);

            if (!isset($raw_configs['data_source'])) throw new ConfigLoadingException('Missing "data_source" field');
            if (!isset($raw_configs['data_source']['type'])) throw new ConfigLoadingException('Missing "data_source/type" field');
            if (!isset($raw_configs['host_name'])) throw new ConfigLoadingException('Missing "host_name" field');
            if (!isset($raw_configs['base_uri'])) throw new ConfigLoadingException('Missing "base_uri" field');
            if (!isset($raw_configs['display_errors'])) throw new ConfigLoadingException('Missing "display_errors" field');

            $this->hostName = $raw_configs['host_name'];
            $this->baseUri = $raw_configs['base_uri'];
            $this->displayError = $raw_configs['display_errors'] === 0 ? 0 : 1;

            $this->dataSourceConfigService = match ($raw_configs['data_source']['type']) {
                'oci' => new OracleDataSourceConfigService($raw_configs['data_source']),
                default => throw new ConfigLoadingException('Unknown data source type: ' . $raw_configs['data_source']['type'])
            };
        } catch (Exception $ex) {
            throw new ConfigLoadingException('Could not parse configs', $ex);
        }
    }

    #[Override]
    public function getDataSourceConfigService(): IDataSourceConfigService {
        return $this->dataSourceConfigService;
    }

    #[Override]
    public function getHostName(): string {
        return $this->hostName;
    }

    #[Override]
    public function getBaseUri(): string {
        return $this->baseUri;
    }

    #[Override]
    public function getDisplayError(): int {
        return $this->displayError;
    }
}