<?php

namespace murica_bl_impl\DataSource\Factories;

use Exception;
use murica_bl\DataSource\Exceptions\DataSourceException;
use murica_bl\DataSource\Factories\IDataSourceFactory;
use murica_bl\DataSource\IDataSource;
use murica_bl\Services\ConfigService\EDataSourceTypes;
use murica_bl\Services\ConfigService\IConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use Override;

class DataSourceFactory implements IDataSourceFactory {
    //region Properties
    private IConfigService $configs;
    //endregion

    //region Constructor
    public function __construct(IConfigService $configService) {
        $this->configs = $configService;
    }
    //endregion


    /**
     * @inheritDoc
     */
    #[Override]
    public function createDataSource(): IDataSource {
        try {
            return match ($this->configs->getDataSourceConfigService()->getType()) {
                EDataSourceTypes::Oracle => new OracleDataSource($this->configs->getDataSourceConfigService())
            };
        } catch (Exception $ex) {
            throw new DataSourceException('Could not create data source', $ex);
        }
    }
}