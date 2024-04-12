<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ITakenExamDao;
use murica_bl\Dto\ITakenExam;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Exam;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\Subject;
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
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
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
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
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

        $sql = sprintf("SELECT TKN.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS USER_EMAIL, USR.%s AS USER_PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS USER_BIRTH_DATE, 
                               TKN.%s AS PROGRAMME_NAME, TKN.%s AS PROGRAMME_TYPE, PRG.%s AS NO_TERMS,
                               STD.%s AS START_TERM, TKN.%s AS EXAM_ID, TKN.%s AS SUBJECT_ID,
                               EXAM.%s AS START_TIME, EXAM.%s AS END_TIME, EXAM.%s AS TEACHER_ID,
                               TCHR.%s AS TEACHER_NAME, TCHR.%s AS TEACHER_EMAIL, TCHR.%s AS TEACHER_PASSWORD, TO_CHAR(TCHR.%s,'YYYY-MM-DD') AS TEACHER_BIRTH_DATE,
                               SUB.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS SUBJECT_TYPE, EXAM.%s AS ROOM_ID, ROOM.%s AS CAPACITY
                               FROM %s.%s USR, %s.%s EXAM, %s.%s TKN, %s.%s STD,
                               %s.%s PRG, %s.%s TCHR, %s.%s SUB, %s.%s ROOM
                               WHERE TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = USR.%s AND
                               TKN.%s = PRG.%s AND TKN.%s = PRG.%s AND TKN.%s = EXAM.%s AND TKN.%s = EXAM.%s AND EXAM.%s = SUB.%s AND EXAM.%s = TCHR.%s AND EXAM.%s = ROOM.%s;",
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
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_NAME,
                       TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
                       TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
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
            $res[] = new TakenExam(
                new Student(
                    new User(
                        oci_result($stmt, 'USER_ID'),
                        oci_result($stmt, 'USER_NAME'),
                        oci_result($stmt, 'USER_EMAIL'),
                        oci_result($stmt, 'USER_PASSWORD'),
                        oci_result($stmt, 'USER_BIRTH_DATE')),
                    new Programme(
                        oci_result($stmt, 'PROGRAMME_NAME'),
                        oci_result($stmt, 'PROGRAMME_TYPE'),
                        oci_result($stmt, 'NO_TERMS')),
                    oci_result($stmt, 'START_TERM')),
                new Exam(
                    new Subject(
                        oci_result($stmt, 'SUBJECT_ID'),
                        oci_result($stmt, 'SUBJECT_NAME'),
                        oci_result($stmt, 'APPROVAL'),
                        oci_result($stmt, 'CREDIT'),
                        oci_result($stmt, 'SUBJECT_TYPE')),
                    oci_result($stmt, 'EXAM_ID'),
                    oci_result($stmt, 'START_TIME'),
                    oci_result($stmt, 'END_TIME'),
                    new User(
                        oci_result($stmt, 'TEACHER_ID'),
                        oci_result($stmt, 'TEACHER_NAME'),
                        oci_result($stmt, 'TEACHER_EMAIL'),
                        oci_result($stmt, 'TEACHER_PASSWORD'),
                        oci_result($stmt, 'TEACHER_BIRTH_DATE')),
                    new Room(
                        oci_result($stmt, 'ROOM_ID'),
                        oci_result($stmt, 'ROOM_CAPACITY'))
                )
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


        $sql = sprintf("SELECT TKN.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS USER_EMAIL, USR.%s AS USER_PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS USER_BIRTH_DATE, 
                               TKN.%s AS PROGRAMME_NAME, TKN.%s AS PROGRAMME_TYPE, PRG.%s AS NO_TERMS,
                               STD.%s AS START_TERM, TKN.%s AS EXAM_ID, TKN.%s AS SUBJECT_ID,
                               EXAM.%s AS START_TIME, EXAM.%s AS END_TIME, EXAM.%s AS TEACHER_ID,
                               TCHR.%s AS TEACHER_NAME, TCHR.%s AS TEACHER_EMAIL, TCHR.%s AS TEACHER_PASSWORD, TO_CHAR(TCHR.%s,'YYYY-MM-DD') AS TEACHER_BIRTH_DATE,
                               SUB.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS SUBJECT_TYPE, EXAM.%s AS ROOM_ID, ROOM.%s AS CAPACITY
                               FROM %s.%s USR, %s.%s EXAM, %s.%s TKN, %s.%s STD,
                               %s.%s PRG, %s.%s TCHR, %s.%s SUB, %s.%s ROOM
                               WHERE TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = USR.%s AND
                               TKN.%s = PRG.%s AND TKN.%s = PRG.%s AND TKN.%s = EXAM.%s AND TKN.%s = EXAM.%s AND EXAM.%s = SUB.%s AND EXAM.%s = TCHR.%s AND EXAM.%s = ROOM.%s;",
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
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_NAME,
                       TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
                       TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID
        );

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $examId = $model->getExam()->getId();
        $subjectId = $model->getExam()->getSubject()->getId();

        if (isset($userId)) $crits[] = "EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . " LIKE :userId";
        if (isset($programmeName)) $crits[] = "EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME . " LIKE :programmeName";
        if (isset($programmeType)) $crits[] = "EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE . " LIKE :programmeType";
        if (isset($examId)) $crits[] = "EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " LIKE :examId";
        if (isset($subjectId)) $crits[] = "EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($userId) && !oci_bind_by_name($stmt, ':userId', $userId, -1))
            throw new DataAccessException('bind userId ' . json_encode(oci_error($stmt)));
        if (isset($programmeName) && !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1))
            throw new DataAccessException('bind programmeName ' . json_encode(oci_error($stmt)));
        if (isset($programmeType) && !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1))
            throw new DataAccessException('bind programmeType ' . json_encode(oci_error($stmt)));
        if (isset($examId) && !oci_bind_by_name($stmt, ':examId', $examId, -1))
            throw new DataAccessException('bind examId ' . json_encode(oci_error($stmt)));
        if (isset($subjectId) && !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
            throw new DataAccessException('bind subjectId ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new TakenExam(
                new Student(
                    new User(
                        oci_result($stmt, 'USER_ID'),
                        oci_result($stmt, 'USER_NAME'),
                        oci_result($stmt, 'USER_EMAIL'),
                        oci_result($stmt, 'USER_PASSWORD'),
                        oci_result($stmt, 'USER_BIRTH_DATE')),
                    new Programme(
                        oci_result($stmt, 'PROGRAMME_NAME'),
                        oci_result($stmt, 'PROGRAMME_TYPE'),
                        oci_result($stmt, 'NO_TERMS')),
                    oci_result($stmt, 'START_TERM')),
                new Exam(
                    new Subject(
                        oci_result($stmt, 'SUBJECT_ID'),
                        oci_result($stmt, 'SUBJECT_NAME'),
                        oci_result($stmt, 'APPROVAL'),
                        oci_result($stmt, 'CREDIT'),
                        oci_result($stmt, 'SUBJECT_TYPE')),
                    oci_result($stmt, 'EXAM_ID'),
                    oci_result($stmt, 'START_TIME'),
                    oci_result($stmt, 'END_TIME'),
                    new User(
                        oci_result($stmt, 'TEACHER_ID'),
                        oci_result($stmt, 'TEACHER_NAME'),
                        oci_result($stmt, 'TEACHER_EMAIL'),
                        oci_result($stmt, 'TEACHER_PASSWORD'),
                        oci_result($stmt, 'TEACHER_BIRTH_DATE')),
                    new Room(
                        oci_result($stmt, 'ROOM_ID'),
                        oci_result($stmt, 'ROOM_CAPACITY'))
                )
            );
        }

        return $res;
    }
    //endregion
}
