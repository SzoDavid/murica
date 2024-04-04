<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\IUser;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\User;
use Override;

class OracleUserDao implements IUserDao {
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

    //region IUserDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(IUser $model): IUser {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s) VALUES (:id, :name, :email, :password, TO_DATE(:birth_date, 'YYYY-MM-DD'))",
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();
        $name = $model->getName();
        $email = $model->getEmail();
        $password = $model->getPassword();
        $birth_date = $model->getBirthDate();

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':email', $email, -1) ||
            !oci_bind_by_name($stmt, ':password', $password, -1) ||
            !oci_bind_by_name($stmt, ':birth_date', $birth_date, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new User($model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(IUser $model): IUser {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :name, %s = :email, %s = :password, %s = TO_DATE(:birth_date, 'YYYY-MM-DD') WHERE %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::USER_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();
        $name = $model->getName();
        $email = $model->getEmail();
        $password = $model->getPassword();
        $birth_date = $model->getBirthDate();

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':email', $email, -1) ||
            !oci_bind_by_name($stmt, ':password', $password, -1) ||
            !oci_bind_by_name($stmt, ':birth_date', $birth_date, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new User($model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(IUser $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();

        if (!oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $res = array();

        $sql = sprintf("SELECT %s AS ID, %s AS NAME, %s AS EMAIL, %s AS PASSWORD, TO_CHAR(%s,'YYYY-MM-DD') AS BIRTH_DATE FROM %s.%s",
            TableDefinition::USER_TABLE_FIELD_ID,
            TableDefinition::USER_TABLE_FIELD_NAME,
            TableDefinition::USER_TABLE_FIELD_EMAIL,
            TableDefinition::USER_TABLE_FIELD_PASSWORD,
            TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
            $this->configService->getTableOwner(),
            TableDefinition::USER_TABLE
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new User(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'EMAIL'),
                oci_result($stmt, 'PASSWORD'),
                oci_result($stmt, 'BIRTH_DATE')
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IUser $model): array {
        $res = array();
        $crits = array();

        $sql = sprintf("SELECT %s AS ID, %s AS NAME, %s AS EMAIL, %s AS PASSWORD, TO_CHAR(%s,'YYYY-MM-DD') AS BIRTH_DATE 
                              FROM %s.%s",
            TableDefinition::USER_TABLE_FIELD_ID,
            TableDefinition::USER_TABLE_FIELD_NAME,
            TableDefinition::USER_TABLE_FIELD_EMAIL,
            TableDefinition::USER_TABLE_FIELD_PASSWORD,
            TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
            $this->configService->getTableOwner(),
            TableDefinition::USER_TABLE,
        );

        $id = $model->getId();
        $name = $model->getName();
        $email = $model->getEmail();

        if (isset($id)) $crits[] = "ID LIKE :id";
        if (isset($name)) $crits[] = "Name LIKE :name";
        if (isset($email)) $crits[] = "EMAIL LIKE :email";
        // NOTE: I did not implement searching by password hash or birth date because it seems useless

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($id) && !oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));
        if (isset($name) && !oci_bind_by_name($stmt, ':name', $name, -1))
            throw new DataAccessException('bind name ' . json_encode(oci_error($stmt)));
        if (isset($email) && !oci_bind_by_name($stmt, ':email', $email, -1))
            throw new DataAccessException('bind email ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new User(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'EMAIL'),
                oci_result($stmt, 'PASSWORD'),
                oci_result($stmt, 'BIRTH_DATE')
            );
        }

        return $res;
    }
    //endregion
}
