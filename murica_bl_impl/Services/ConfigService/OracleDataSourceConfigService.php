<?php

namespace murica_bl_impl\Services\ConfigService;

use Exception;
use murica_bl\Services\ConfigService\EDataSourceTypes;
use murica_bl\Services\ConfigService\Exceptions\ConfigLoadingException;
use murica_bl\Services\ConfigService\IDataSourceConfigService;

class OracleDataSourceConfigService implements IDataSourceConfigService
{
    //region Properties
    private string $user;
    private string $password;
    private string $connectionString;
    private array $tableNames;
    //endregion

    //region Ctor
    /**
     * @throws ConfigLoadingException
     */
    public function __construct(array $config)
    {
        try {
            if (!isset($raw_configs['user'])) throw new ConfigLoadingException('Missing "user" field');
            if (!isset($raw_configs['password'])) throw new ConfigLoadingException('Missing "password" field');
            if (!isset($raw_configs['connection_string'])) throw new ConfigLoadingException('Missing "connection_string" field');

            $this->user = $config['user'];
            $this->password = $config['password'];
            $this->connectionString = $config['connection_string'];
            $this->tableNames['user'] = $config['tables']['user'];
        } catch (Exception $ex) {
            throw new ConfigLoadingException('Could not load data source config', $ex);
        }
    }
    //endregion

    //region Getters
    #[\Override]
    public function getType(): EDataSourceTypes
    {
        return EDataSourceTypes::Oracle;
    }

    #[\Override]
    public function getUserTableName(): string
    {
        return $this->tableNames['user'];
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getConnectionString(): string
    {
        return $this->connectionString;
    }
    //endregion
}