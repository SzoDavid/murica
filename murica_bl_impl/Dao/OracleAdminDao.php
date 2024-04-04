<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IAdmin;
use murica_bl\Dto\IUser;
use murica_bl\Exceptions\NotImplementedException;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Admin;
use murica_bl_impl\Dto\User;
use Override;

class OracleAdminDao implements IAdminDao {
//region Properties
    private OracleDataSource $dataSource;
    private IDataSourceConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(OracleDataSource $dataSource, IDataSourceConfigService $configService) {
        $this->dataSource = $dataSource;
        $this->configService = $configService;
    }
    //endregion

    //region IAdminDao members
    #[Override]
    public function create(IAdmin $model): IAdmin {
        if (!$this->checkIfUserExists($model->getUser()))
            throw new ValidationException('User with id ' . $model->getUser()->getId() . ' does not exist in datasource');

        $sql = sprintf("INSERT INTO %s.%s (%s) VALUES (:id)",
                       $this->configService->getTableOwner(),
                       TableDefinition::ADMIN_TABLE,
                       TableDefinition::ADMIN_TABLE_FIELD_USER_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getUser()->getId();

        if (!oci_bind_by_name($stmt, ':id', $id, -1))
            if (!oci_execute($stmt))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit($model)[0];
    }

    #[Override]
    public function delete(IAdmin $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::ADMIN_TABLE,
                       TableDefinition::ADMIN_TABLE_FIELD_USER_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getUser()->getId();

        if (!oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));
    }

    #[Override]
    public function findAll(): array {
        $res = array();

        $sql = sprintf("SELECT USR.%s AS ID, USR.%s AS NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE FROM %s.%s USR, %s.%s ADMIN WHERE USR.%s = ADMIN.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ADMIN_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::ADMIN_TABLE_FIELD_USER_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Admin(new User(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'EMAIL'),
                oci_result($stmt, 'PASSWORD'),
                oci_result($stmt, 'BIRTH_DATE')
            ));
        }

        return $res;
    }

    /**
     * @throws NotImplementedException
     */
    #[Override]
    public function findByCrit(IAdmin $model): array {
        // TODO: Implement findByCrit() method.
        throw new NotImplementedException();
    }
    //endregion

    //region Private members
    /**
     * @throws DataAccessException
     */
    public function checkIfUserExists(IUser $user): bool {
        $sql = sprintf("SELECT * FROM %s.%s WHERE %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        $id = $user->getId();

        if (!oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        return oci_fetch($stmt);
    }
    //endregion
}