<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IStudentDao;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Student;
use murica_bl\Dto\IStudent;
use murica_bl_impl\Dto\User;
use Override;

class OracleStudentDao implements IStudentDao {
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

    //region IStudentDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(IStudent $model): IStudent {
        $model->validate();
        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s) VALUES (:userId, :programmeName, :programmeType, :startTerm)",
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM
        );

        $stmt = oci_parse($this->dataSource->getConnection(), $sql);
        if (!$stmt) {
            $error = oci_error();
            throw new DataAccessException("Error occurred during statement preparation: " . $error['message'], $error['code']);
        }
        $IUser = $model->getUser();
        oci_bind_by_name($stmt, ":userId", $IUser);
        $programmeName = $model->getProgrammeName();
        oci_bind_by_name($stmt, ":programmeName", $programmeName);
        $programmeType = $model->getProgrammeType();
        oci_bind_by_name($stmt, ":programmeType", $programmeType);
        $startTerm = $model->getStartTerm();
        oci_bind_by_name($stmt, ":startTerm", $startTerm);

        $success = oci_execute($stmt);

        if (!oci_bind_by_name($stmt, ':user_id', $IUser, -1) ||
            !oci_bind_by_name($stmt, ':programme_name', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programme_type', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':start_term', $startTerm, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!$stmt) {
            throw new DataAccessException(oci_error($stmt));
        }

        if (!$success) {
            $error = oci_error($stmt);
            throw new DataAccessException("Error occurred during query execution: " . $error['message'], $error['code']);
        }

        return $model;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(IStudent $model): IStudent {
        $model->validate();
        $sql = sprintf("UPDATE %s.%s SET %s = :programmeName, %s = :programmeType, %s = :startTerm WHERE %s = :userId",
            $this->configService->getTableOwner(),
            TableDefinition::STUDENT_TABLE,
            TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
            TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
            TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
            TableDefinition::STUDENT_TABLE_FIELD_USER_ID
        );

        $stmt = oci_parse($this->dataSource->getConnection(), $sql);
        if (!$stmt) {
            $error = oci_error();
            throw new DataAccessException("Error occurred during statement preparation: " . $error['message'], $error['code']);
        }
        $IUser = $model->getUser();
        oci_bind_by_name($stmt, ":userId", $IUser);
        $programmeName = $model->getProgrammeName();
        oci_bind_by_name($stmt, ":programmeName", $programmeName);
        $programmeType = $model->getProgrammeType();
        oci_bind_by_name($stmt, ":programmeType", $programmeType);
        $startTerm = $model->getStartTerm();
        oci_bind_by_name($stmt, ":startTerm", $startTerm);

        $success = oci_execute($stmt);

        if (!oci_bind_by_name($stmt, ':user_id', $IUser, -1) ||
            !oci_bind_by_name($stmt, ':programme_name', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programme_type', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':start_term', $startTerm, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!$stmt) {
            throw new DataAccessException(oci_error($stmt));
        }

        if (!$success) {
            $error = oci_error($stmt);
            throw new DataAccessException("Error occurred during query execution: " . $error['message'], $error['code']);
        }

        return $model;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(IStudent $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :userId",
            $this->configService->getTableOwner(),
            TableDefinition::STUDENT_TABLE,
            TableDefinition::STUDENT_TABLE_FIELD_USER_ID
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getUser()->getId();

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

        $sql = sprintf("SELECT USR.%s AS ID, USR.%s AS NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                STD.%s AS PROGRAMME_NAME, STD.%s AS PROGRAMME_TYPE, STD.%s AS START_TERM FROM %s.%s USR, %s.%s STD WHERE USR.%s = STD.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Student(new User(
                                     oci_result($stmt, 'ID'),
                                     oci_result($stmt, 'NAME'),
                                     oci_result($stmt, 'EMAIL'),
                                     oci_result($stmt, 'PASSWORD'),
                                     oci_result($stmt, 'BIRTH_DATE')),
                                     oci_result($stmt, 'PROGRAMME_NAME'),
                                     oci_result($stmt, 'PROGRAMME_TYPE'),
                                     oci_result($stmt, 'START_TERM')
            );
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IStudent $model): array {
        $res = array();
        $crits = array();

        $sql = sprintf("SELECT USR.%s AS ID, USR.%s AS NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                STD.%s AS PROGRAMME_NAME, STD.%s AS PROGRAMME_TYPE, STD.%s AS START_TERM FROM %s.%s USR, %s.%s STD WHERE USR.%s = STD.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID);

        $id = $model->getUser()->getId();
        $programmename = $model->getProgrammeName();
        $programmetype = $model->getProgrammeType();
        $startterm = $model->getStartTerm();
        if (isset($id)) $crits[] = TableDefinition::USER_TABLE_FIELD_ID . " LIKE :id";
        if (isset($programmetype)) $crits[] = TableDefinition::USER_TABLE_FIELD_NAME . " LIKE :prorammeType";
        if (isset($programmename)) $crits[] = TableDefinition::USER_TABLE_FIELD_EMAIL . " LIKE :programmeName";
        if (isset($startterm)) $crits[] = TableDefinition::USER_TABLE_FIELD_NAME . " LIKE :startTerm";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (isset($id) && !oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));
        if (isset($name) && !oci_bind_by_name($stmt, ':programmeType', $name, -1))
            throw new DataAccessException('bind programmeType' . json_encode(oci_error($stmt)));
        if (isset($email) && !oci_bind_by_name($stmt, ':programmeName', $email, -1))
            throw new DataAccessException('bind programmeName ' . json_encode(oci_error($stmt)));
        if (isset($email) && !oci_bind_by_name($stmt, ':startterm', $email, -1))
            throw new DataAccessException('bind startTerm ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Student(new User(
                                   oci_result($stmt, 'ID'),
                                   oci_result($stmt, 'NAME'),
                                   oci_result($stmt, 'EMAIL'),
                                   oci_result($stmt, 'PASSWORD'),
                                   oci_result($stmt, 'BIRTH_DATE')),
                                 oci_result($stmt, 'PROGRAMME_NAME'),
                                 oci_result($stmt, 'PROGRAMME_TYPE'),
                                 oci_result($stmt, 'START_TERM'),
            );
        }

        return $res;
    }
    //endregion
}



