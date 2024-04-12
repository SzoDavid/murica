<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IExamDao;
use murica_bl\Dto\IExam;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Exam;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleExamDao implements IExamDao {
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
    public function create(IExam $model): IExam {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s, %s) VALUES (:subjectId, :id, TO_DATE(:startTime, 'YYYY-MM-DD HH24:MI'), TO_DATE(:endTime, 'YYYY-MM-DD HH24:MI'), :teacherId, :roomId)",
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_START_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_END_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $startTime = $model->getStartTime();
        $endTime = $model->getEndTime();
        $teacherId = $model->getTeacher()->getId();
        $roomId = $model->getRoom()->getId();

        if (!oci_bind_by_name($stmt, ':subjectId', $subjectId, -1) ||
            !oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':startTime', $startTime, -1) ||
            !oci_bind_by_name($stmt, ':endTime', $endTime, -1) ||
            !oci_bind_by_name($stmt, ':teacherId', $teacherId, -1) ||
            !oci_bind_by_name($stmt, ':roomId', $roomId, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Exam($model->getSubject(), $model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(IExam $model): IExam {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :teacherId, %s = :roomId WHERE %s = :subjectId AND %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $startTime = $model->getStartTime();
        $endTime = $model->getEndTime();
        $teacherId = $model->getTeacher()->getId();
        $roomId = $model->getRoom()->getId();

        if (!oci_bind_by_name($stmt, ':subjectId', $subjectId, -1) ||
            !oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':startTime', $startTime, -1) ||
            !oci_bind_by_name($stmt, ':endTime', $endTime, -1) ||
            !oci_bind_by_name($stmt, ':teacherId', $teacherId, -1) ||
            !oci_bind_by_name($stmt, ':roomId', $roomId, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Exam($model->getSubject(), $model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(IExam $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :subjectId AND %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();

        if (!oci_bind_by_name($stmt, ':subjectId', $subjectId, -1) ||
            !oci_bind_by_name($stmt, ':id', $id, -1))
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

        $sql = sprintf("SELECT EXAM.%s AS EXAM_ID, EXAM.%s AS SUBJECT_ID, SUB. %s AS SUBJECT_NAME, 
                                SUB.%s AS CREDIT, SUB.%s AS TYPE, TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS START_TIME,
                                TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS END_TIME, EXAM.%s AS TEACHER_ID, 
                                USR.%s AS TEACHER_NAME, USR.%s AS EMAIL, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                EXAM.%s AS ROOM_ID, ROOM.%s AS CAPACITY 
                                FROM %s.%s USR, %s.%s EXAM, %s.%s SUB, %s.%s ROOM
                                WHERE EXAM.%s = SUB.%s AND EXAM.%s = USR.%s AND EXAM.%s = ROOM.%s",
            TableDefinition::EXAM_TABLE_FIELD_ID,
            TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
            TableDefinition::SUBJECT_TABLE_FIELD_NAME,
            TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
            TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
            TableDefinition::EXAM_TABLE_FIELD_START_TIME,
            TableDefinition::EXAM_TABLE_FIELD_END_TIME,
            TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
            TableDefinition::USER_TABLE_FIELD_NAME,
            TableDefinition::USER_TABLE_FIELD_EMAIL,
            TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
            TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
            TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
            $this->configService->getTableOwner(),
            TableDefinition::USER_TABLE,
            $this->configService->getTableOwner(),
            TableDefinition::EXAM_TABLE,
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE,
            $this->configService->getTableOwner(),
            TableDefinition::ROOM_TABLE,
            TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
            TableDefinition::SUBJECT_TABLE_FIELD_ID,
            TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
            TableDefinition::USER_TABLE_FIELD_ID,
            TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
            TableDefinition::ROOM_TABLE_FIELD_ID
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Exam(
                new Subject(
                    oci_result($stmt, 'SUBJECT_ID'),
                    oci_result($stmt, 'SUBJECT_NAME'),
                    oci_result($stmt, 'SUBJECT_CREDIT'),
                    oci_result($stmt, 'SUBJECT_TYPE')),
                oci_result($stmt, 'EXAM_ID'),
                oci_result($stmt, 'START_TIME'),
                oci_result($stmt, 'END_TIME'),
                new User(
                    oci_result($stmt, 'TEACHER_ID'),
                    oci_result($stmt, 'TEACHER_NAME'),
                    oci_result($stmt, 'EMAIL'),
                    oci_result($stmt, 'BIRTH_DATE')),
                new Room(
                    oci_result($stmt, 'ROOM_ID'),
                    oci_result($stmt, 'CAPACITY'))
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IExam $model): array {
        $res = array();
        $crits = array();

        $sql = sprintf("SELECT EXAM.%s AS EXAM_ID, EXAM.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, 
                                SUB.%s AS CREDIT, SUB.%s AS TYPE, TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS START_TIME,
                                TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS END_TIME, EXAM.%s AS TEACHER_ID, 
                                USR.%s AS TEACHER_NAME, USR.%s AS EMAIL, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                EXAM.%s AS ROOM_ID, ROOM.%s AS CAPACITY 
                                FROM %s.%s USR, %s.%s EXAM, %s.%s SUB, %s.%s ROOM
                                WHERE EXAM.%s = SUB.%s AND EXAM.%s = USR.%s AND EXAM.%s = ROOM.%s",
                       TableDefinition::EXAM_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_NAME,
                       TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::EXAM_TABLE_FIELD_START_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_END_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID
        );

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $startTime = $model->getStartTime();
        $endTime = $model->getEndTime();
        $teacherId = $model->getTeacher()->getId();
        $roomId = $model->getRoom()->getId();

        if (isset($subjectId)) $crits[] = "EXAM.".TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";
        if (isset($id)) $crits[] = "EXAM.".TableDefinition::EXAM_TABLE_FIELD_ID . " LIKE :id";
        if (isset($startTime)) $crits[] = "EXAM.".TableDefinition::EXAM_TABLE_FIELD_START_TIME . " LIKE :startTime";
        if (isset($endTime)) $crits[] = "EXAM.".TableDefinition::EXAM_TABLE_FIELD_END_TIME . " LIKE :endTime";
        if (isset($teacherId)) $crits[] = "EXAM.".TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " LIKE :teacherId";
        if (isset($roomId)) $crits[] = "EXAM.".TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " LIKE :roomId";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($subjectId) && !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
            throw new DataAccessException('bind subjectId ' . json_encode(oci_error($stmt)));
        if (isset($id) && !oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));
        if (isset($startTime) && !oci_bind_by_name($stmt, ':startTime', $startTime, -1))
            throw new DataAccessException('bind startTime ' . json_encode(oci_error($stmt)));
        if (isset($endTime) && !oci_bind_by_name($stmt, ':endTime', $endTime, -1))
            throw new DataAccessException('bind endTime ' . json_encode(oci_error($stmt)));
        if (isset($teacherId) && !oci_bind_by_name($stmt, ':teacherId', $teacherId, -1))
            throw new DataAccessException('bind teacherId ' . json_encode(oci_error($stmt)));
        if (isset($roomId) && !oci_bind_by_name($stmt, ':roomId', $roomId, -1))
            throw new DataAccessException('bind roomId ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Exam(
                new Subject(
                    oci_result($stmt, 'SUBJECT_ID'),
                    oci_result($stmt, 'SUBJECT_NAME'),
                    oci_result($stmt, 'SUBJECT_CREDIT'),
                    oci_result($stmt, 'SUBJECT_TYPE')),
                oci_result($stmt, 'EXAM_ID'),
                oci_result($stmt, 'START_TIME'),
                oci_result($stmt, 'END_TIME'),
                new User(
                    oci_result($stmt, 'TEACHER_ID'),
                    oci_result($stmt, 'TEACHER_NAME'),
                    oci_result($stmt, 'EMAIL'),
                    oci_result($stmt, 'BIRTH_DATE')),
                new Room(
                    oci_result($stmt, 'ROOM_ID'),
                    oci_result($stmt, 'CAPACITY'))
            );
        }

        return $res;
    }
    //endregion
}
