<?php

namespace murica_bl_impl\DataSource;

use Exception;
use murica_bl\Dao\ITokenDao;
use murica_bl\Dao\IUserDao;
use murica_bl\DataSource\Exceptions\DataSourceException;
use murica_bl\DataSource\IDataSource;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\Dao\OracleTokenDao;
use murica_bl_impl\Dao\OracleUserDao;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleDataSource implements IDataSource {
    //region Properties
    private IDataSourceConfigService $configService;
    private $connection;
    //endregion

    //region Ctor
    /**
     * @throws DataSourceException
     */
    public function __construct(IDataSourceConfigService $configService) {
        $this->configService = $configService;

        try {
            if (!$configService instanceof OracleDataSourceConfigService) {
                throw new DataSourceException("Data source configs are invalid");
            }

            $this->connection = oci_connect(
                $configService->getUser(),
                $configService->getPassword(),
                $configService->getConnectionString()
            );

            if (!$this->connection) {
                throw new DataSourceException('Failed to establish connection with database: ' . oci_error());
            }
        } catch (Exception $ex) {
            throw new DataSourceException('Could not establish database connection', $ex);
        }
    }

    public function __destruct() {
        oci_close($this->connection);
    }
    //endregion

    #[Override]
    public function createUserDao(): IUserDao {
        return new OracleUserDao($this, $this->configService);
    }

    #[Override]
    public function createTokenDao(): ITokenDao {
        return new OracleTokenDao($this, $this->configService);
    }

    public function getConnection() {
        return $this->connection;
    }
}