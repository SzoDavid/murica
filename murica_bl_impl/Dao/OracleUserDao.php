<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\IUser;
use murica_bl\Orm\Exception\OciException;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleUserDao implements IUserDao {
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

        $id = $model->getId();
        $name = $model->getName();
        $email = $model->getEmail();
        $password = $model->getPassword();
        $birth_date = $model->getBirthDate();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->bind(':name', $name)
                ->bind(':email', $email)
                ->bind(':password', $password)
                ->bind(':birth_date', $birth_date)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create user', $e);
        }

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

        $id = $model->getId();
        $name = $model->getName();
        $email = $model->getEmail();
        $password = $model->getPassword();
        $birth_date = $model->getBirthDate();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->bind(':name', $name)
                ->bind(':email', $email)
                ->bind(':password', $password)
                ->bind(':birth_date', $birth_date)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to update user', $e);
        }

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

        $id = $model->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create user', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $sql = sprintf("SELECT %s AS ID, %s AS NAME, %s AS EMAIL, %s AS PASSWORD, TO_CHAR(%s,'YYYY-MM-DD') AS BIRTH_DATE FROM %s.%s",
            TableDefinition::USER_TABLE_FIELD_ID,
            TableDefinition::USER_TABLE_FIELD_NAME,
            TableDefinition::USER_TABLE_FIELD_EMAIL,
            TableDefinition::USER_TABLE_FIELD_PASSWORD,
            TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
            $this->configService->getTableOwner(),
            TableDefinition::USER_TABLE
        );

        try {
            $users = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query users', $e);
        }

        return $this->fetchUsers($users);
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
            TableDefinition::USER_TABLE
        );

        $id = $model->getId();
        $name = $model->getName();
        $email = $model->getEmail();

        if (isset($id)) $crits[] = TableDefinition::USER_TABLE_FIELD_ID . " LIKE :id";
        if (isset($name)) $crits[] = TableDefinition::USER_TABLE_FIELD_NAME . " LIKE :name";
        if (isset($email)) $crits[] = TableDefinition::USER_TABLE_FIELD_EMAIL . " LIKE :email";
        // NOTE: I did not implement searching by password hash or birth date because it seems useless

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($id)) $stmt->bind(':id', $id);
            if (isset($name)) $stmt->bind(':name', $name);
            if (isset($email)) $stmt->bind(':email', $email);

            $users = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query taken exams', $e);
        }

        return $this->fetchUsers($users);
    }
    //endregion

    private function fetchUsers(array $users): array {
        $res = array();

        foreach ($users as $user) {
            $res[] = new User(
                $user['ID'],
                $user['NAME'],
                $user['EMAIL'],
                $user['PASSWORD'],
                $user['BIRTH_DATE']);
        }

        return $res;
    }
}
