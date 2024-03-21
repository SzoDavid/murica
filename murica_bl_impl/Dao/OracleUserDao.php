<?php

namespace murica_bl_impl\Dao;

use murica_bl\Dao\IUserDao;
use murica_bl\Dto\IUser;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\User;
use Override;

class OracleUserDao implements IUserDao
{
    //region Properties
    private OracleDataSource $dataSource;
    private IDataSourceConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(OracleDataSource $dataSource, IDataSourceConfigService $configService)
    {
        $this->dataSource = $dataSource;
        $this->configService = $configService;
    }
    //endregion

    //region SELECT
    #[Override]
    public function findAll(): array
    {
        $res = array();

        $stmt = oci_parse($this->dataSource->getConnection(), 'SELECT * FROM ' . $this->configService->getUserTableName());
        oci_execute($stmt, OCI_DEFAULT);
        while (oci_fetch($stmt)) {
            $res[] = new User(
                oci_result($stmt, "ID"),
                oci_result($stmt, "NAME"),
                oci_result($stmt, "EMAIL"),
                oci_result($stmt, "PASSWORD"),
                oci_result($stmt, "BIRTH_DATE")
            );
        }

        return $res;
    }

    #[Override]
    public function findByCrit(IUser $model): IUser
    {
        return new User("YTWK3B", "Szobonya Dávid", "szobonya.david@gmail.com", "asd", "2003-05-22");
    }
    //endregion


    #[Override]
    public function insert(IUser $model): IUser
    {
        return new User("YTWK3B", "Szobonya Dávid", "szobonya.david@gmail.com", "asd", "2003-05-22");
    }

    #[Override]
    public function remove(IUser $model): void
    {
        // TODO: Implement remove() method.
    }
}