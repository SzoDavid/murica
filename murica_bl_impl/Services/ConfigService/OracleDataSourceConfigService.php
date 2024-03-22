<?php

namespace murica_bl_impl\Services\ConfigService;

use Exception;
use murica_bl\Services\ConfigService\EDataSourceTypes;
use murica_bl\Services\ConfigService\Exceptions\ConfigLoadingException;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use Override;

class OracleDataSourceConfigService implements IDataSourceConfigService {
    //region Properties
    private string $user;
    private string $password;
    private string $connectionString;
    private string $tableOwner;
    //endregion

    //region Ctor
    /**
     * @throws ConfigLoadingException
     */
    public function __construct(array $config) {
        try {
            if (!isset($config['user'])) throw new ConfigLoadingException('Missing "user" field');
            if (!isset($config['password'])) throw new ConfigLoadingException('Missing "password" field');
            if (!isset($config['connection_string'])) throw new ConfigLoadingException('Missing "connection_string" field');
            if (!isset($config['table_owner'])) throw new ConfigLoadingException('Missing "table_owner" field');

            $this->user = $config['user'];
            $this->password = $config['password'];
            $this->connectionString = $config['connection_string'];
            $this->tableOwner = $config['table_owner'];
        } catch (Exception $ex) {
            throw new ConfigLoadingException('Could not load data source config', $ex);
        }
    }
    //endregion

    //region Getters
    #[Override]
    public function getType(): EDataSourceTypes {
        return EDataSourceTypes::Oracle;
    }

    public function getUser(): string {
        return $this->user;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getConnectionString(): string {
        return $this->connectionString;
    }

    public function getTableOwner(): string {
        return $this->tableOwner;
    }
    //endregion
}