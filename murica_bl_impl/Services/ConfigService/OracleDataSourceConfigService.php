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
    private string $encoding;
    private array $tableNames;
    //endregion

    //region Ctor
    /**
     * @throws ConfigLoadingException
     */
    public function __construct(array $config)
    {
        try {
            $this->user = $config['user'];
            $this->password = $config['password'];
            $this->connectionString = $config['connection_string'];
            $this->encoding = $config['encoding'];
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

    public function getEncoding(): string
    {
        return $this->encoding;
    }
    //endregion
}