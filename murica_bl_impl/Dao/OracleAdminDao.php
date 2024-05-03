<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IAdmin;
use murica_bl\Orm\Exception\OciException;
use murica_bl_impl\Dao\Utils\OracleCheckers;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Admin;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleAdminDao implements IAdminDao {
//region Properties
    private OracleDataSource $dataSource;
    private OracleDataSourceConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(OracleDataSource $dataSource, OracleDataSourceConfigService $configService) {
        $this->dataSource = $dataSource;
        $this->configService = $configService;
    }
    //endregion

    //region IAdminDao members
    #[Override]
    public function create(IAdmin $model): IAdmin {
        if (!OracleCheckers::checkIfUserExists($model->getUser(), $this->configService, $this->dataSource))
            throw new ValidationException('User with id ' . $model->getUser()->getId() . ' does not exist in datasource');

        $sql = sprintf("INSERT INTO %s.%s (%s) VALUES (:id)",
                       $this->configService->getTableOwner(),
                       TableDefinition::ADMIN_TABLE,
                       TableDefinition::ADMIN_TABLE_FIELD_USER_ID);

        $id = $model->getUser()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->execute(OCI_COMMIT_ON_SUCCESS);
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create admin', $e);
        }

        return $this->findByCrit($model)[0];
    }

    #[Override]
    public function delete(IAdmin $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::ADMIN_TABLE,
                       TableDefinition::ADMIN_TABLE_FIELD_USER_ID);

        $id = $model->getUser()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->execute(OCI_COMMIT_ON_SUCCESS);
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete admin', $e);
        }
    }

    #[Override]
    public function findAll(): array {
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

        try {
            $admins = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query admins', $e);
        }

        return $this->fetchAdmins($admins);
    }

    #[Override]
    public function findByCrit(IAdmin $model): array {
        $crits = array();

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

        $id = $model->getUser()->getId();
        $name = $model->getUser()->getName();
        $email = $model->getUser()->getEmail();

        if (isset($id)) $crits[] = TableDefinition::USER_TABLE_FIELD_ID . " LIKE :id";
        if (isset($name)) $crits[] = TableDefinition::USER_TABLE_FIELD_NAME . " LIKE :name";
        if (isset($email)) $crits[] = TableDefinition::USER_TABLE_FIELD_EMAIL . " LIKE :email";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($id)) $stmt->bind(':id', $id);
            if (isset($name)) $stmt->bind(':name', $name);
            if (isset($email)) $stmt->bind(':email', $email);

            $admins = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query admins', $e);
        }

        return $this->fetchAdmins($admins);
    }
    //endregion

    private function fetchAdmins(array $admins): array {
        $res = array();

        foreach ($admins as $admin) {
            $res[] = new Admin(
                new User(
                    $admin['ID'],
                    $admin['NAME'],
                    $admin['EMAIL'],
                    $admin['PASSWORD'],
                    $admin['BIRTH_DATE']
                ));
        }

        return $res;
    }
}