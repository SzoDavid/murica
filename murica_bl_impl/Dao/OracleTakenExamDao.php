<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ITakenExamDao;
use murica_bl\Dto\ITakenExam;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\TakenExam;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleTakenExamDao implements ITakenExamDao {
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
    public function create(ITakenExam $model): ITakenExam {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s) VALUES (:userId, :programmeName, :programmeType, :examId, :subjectId)",
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgrammeName();
        $programmeType = $model->getStudent()->getProgrammeType();
        $examId = $model->getExam()->getId();
        $subjectId = $model->getExam()->getSubject()->getId();

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':examId', $examId, -1) ||
            !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new TakenExam($model->getStudent(), $model->getExam()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(ITakenExam $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :userId AND %s = :programmeName AND %s = :programmeType AND %s = :examId AND %s = :subjectId",
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgrammeName();
        $programmeType = $model->getStudent()->getProgrammeType();
        $examId = $model->getExam()->getId();
        $subjectId = $model->getExam()->getSubject()->getId();

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':examId', $examId, -1) ||
            !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
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

        $sql = sprintf("SELECT TKN.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE, 
                               TKN.%s AS PROGRAMME_NAME, TKN.%s AS PROGRAMME_TYPE, PRG.%s AS NO_TERMS
                               STD.%s AS START_TERM, TKN.%s AS EXAM_ID, TKN.%s AS SUBJECT_ID,
                               EXAM.%s AS START_TIME, EXAM.%s AS END_TIME, EXAM.%s AS TEACHER_ID,
                                FROM %s.%s USR",
            TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
            TableDefinition::USER_TABLE_FIELD_NAME,
            TableDefinition::USER_TABLE_FIELD_EMAIL,
            TableDefinition::USER_TABLE_FIELD_PASSWORD,
            TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
            TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
            TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
            TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
            TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
            TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
            TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID,
            TableDefinition::EXAM_TABLE_FIELD_START_TIME,
            TableDefinition::EXAM_TABLE_FIELD_END_TIME,
            TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
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
    public function findByCrit(ITakenExam $model): array {
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
