<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IStudentDao;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Student;
use murica_bl\Dto\IStudent;
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
    public function findAll(): array {
        $res = array();

        $sql = sprintf("SELECT %s AS USER_ID, %s AS PROGRAMME_NAME, %s AS PROGRAMME_TYPE, %s AS START_TERM FROM %s.%s",
            TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
            TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
            TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
            TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
            $this->configService->getTableOwner(),
            TableDefinition::STUDENT_TABLE
        );
        $stmt = oci_parse($this->dataSource->getConnection(), $sql);
        oci_execute($stmt, OCI_DEFAULT);
        if (!$stmt) {
            throw new DataAccessException(oci_error($stmt));
        }
        while (oci_fetch($stmt)) {
            $res[] = new Student(
                oci_result($stmt, 'USER_ID'),
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

        $sql = sprintf("SELECT STD.%s AS USER_ID, STD.%s AS PROGRAMME_NAME, STD.%s AS PROGRAMME_TYPE, STD.%s AS START_TERM FROM %s.%s STD",
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       $this->configService->getTableOwner(),

                       TableDefinition::STUDENT_TABLE);

        $userId = $model->getUserId();
        $programmeName = $model->getProgrammeName();
        $programmeType = $model->getProgrammeType();
        $startTerm = $model->getStartTerm();

        if (isset($userId)) $crits[] = TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " LIKE :userId";
        if (isset($programmeName)) $crits[] = TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " LIKE :programmeName";
        if (isset($programmeType)) $crits[] = TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . " LIKE :programmeType";
        if (isset($startTerm)) $crits[] = TableDefinition::STUDENT_TABLE_FIELD_START_TERM . " = :startTerm";

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (isset($userId) && !oci_bind_by_name($stmt, ':userId', $userId, -1))
            throw new DataAccessException('bind userId ' . json_encode(oci_error($stmt)));
        if (isset($programmeName) && !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1))
            throw new DataAccessException('bind programmeName ' . json_encode(oci_error($stmt)));
        if (isset($programmeType) && !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1))
            throw new DataAccessException('bind programmeType ' . json_encode(oci_error($stmt)));
        if (isset($startTerm) && !oci_bind_by_name($stmt, ':startTerm', $startTerm, -1))
            throw new DataAccessException('bind startTerm ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Student(
                oci_result($stmt, 'USER_ID'),
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
        $userId = $model->getUserId();
        oci_bind_by_name($stmt, ":userId", $userId);
        $programmeName = $model->getProgrammeName();
        oci_bind_by_name($stmt, ":programmeName", $programmeName);
        $programmeType = $model->getProgrammeType();
        oci_bind_by_name($stmt, ":programmeType", $programmeType);
        $startTerm = $model->getStartTerm();
        oci_bind_by_name($stmt, ":startTerm", $startTerm);
        oci_execute($stmt);

        if (!$stmt) {
            throw new DataAccessException(oci_error($stmt));
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
        $programmeName = $model->getProgrammeName();
        oci_bind_by_name($stmt, ":programmeName", $programmeName);
        $programmeType = $model->getProgrammeType();
        oci_bind_by_name($stmt, ":programmeType", $programmeType);
        $startTerm = $model->getStartTerm();
        oci_bind_by_name($stmt, ":startTerm", $startTerm);
        $userId= $model->getUserId();
        oci_bind_by_name($stmt, ":userId", $userId);
        oci_execute($stmt);

        if (!$stmt) {
            throw new DataAccessException(oci_error($stmt));
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

        $stmt = oci_parse($this->dataSource->getConnection(), $sql);
        $userId = $model->getUserId();
        oci_bind_by_name($stmt, ":userId", $userId);
        oci_execute($stmt);

        if (!$stmt) {
            throw new DataAccessException(oci_error($stmt));
        }
    }
    //endregion
}



