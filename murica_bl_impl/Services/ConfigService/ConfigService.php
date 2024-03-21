<?php

namespace murica_bl_impl\Services\ConfigService;

use Exception;
use murica_bl\Services\ConfigService\Exceptions\ConfigLoadingException;
use murica_bl\Services\ConfigService\IConfigService;
use murica_bl\Services\ConfigService\IDataSourceConfigService;

class ConfigService implements IConfigService
{
    //region Properties
    private IDataSourceConfigService $dataSourceConfigService;
    //endregion

    /**
     * @throws ConfigLoadingException
     */
    public function __construct(string $configPath)
    {
        try {
            $raw_configs = json_decode(file_get_contents($configPath), true);

            $this->dataSourceConfigService = match ($raw_configs['data_source']['type']) {
                'oci' => new OracleDataSourceConfigService($raw_configs['data_source']),
                default => throw new ConfigLoadingException('Unknown data source type: ' . $raw_configs['data_source']['type'])
            };
        } catch (Exception $ex) {
            throw new ConfigLoadingException('Could not parse configs', $ex);
        }
    }


    #[\Override]
    public function getDataSourceConfigService(): IDataSourceConfigService
    {
        return $this->dataSourceConfigService;
    }
}