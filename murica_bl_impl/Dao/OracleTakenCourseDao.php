<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ITakenCourseDao;
use murica_bl\Dto\ITakenCourse;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Course;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\TakenCourse;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleTakenCourseDao implements ITakenCourseDao {
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

    //region ITakenCourseDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(ITakenCourse $model): ITakenCourse {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s, %s, %s) VALUES (:userId, :programmeName, :programmeType, :courseId, :subjectId, :grade, :approved)",
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKENCOURSE_TABLE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();
        $grade = $model->getGrade();
        $approved = $model->isApproved() ? 1 : 0;

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':courseId', $courseId, -1) ||
            !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1) ||
            !oci_bind_by_name($stmt, ':grade', $grade, -1) ||
            !oci_bind_by_name($stmt, ':approved', $approved, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new TakenCourse($model->getStudent(), $model->getCourse()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(ITakenCourse $model): ITakenCourse {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :grade, %s = :approved WHERE %s = :userId AND %s = :programmeName AND %s = :programmeType AND %s = :courseId AND %s = :subjectId",
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKENCOURSE_TABLE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();
        $grade = $model->getGrade();
        $approved = $model->isApproved() ? 1 : 0;

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':courseId', $courseId, -1) ||
            !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1) ||
            !oci_bind_by_name($stmt, ':grade', $grade, -1) ||
            !oci_bind_by_name($stmt, ':approved', $approved, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new TakenCourse($model->getStudent(), $model->getCourse()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(ITakenCourse $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :userId AND %s = :programmeName AND %s = :programmeType AND %s = :courseId AND %s = :subjectId",
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKENCOURSE_TABLE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':courseId', $courseId, -1) ||
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

        $sql = sprintf("SELECT USR.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                TKN.%s AS PROGRAMME_NAME, TKN.%s AS PROGRAMME_TYPE, STD.%s AS START_TERM, PRG.%s AS NO_TERMS,
                                TKN.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS TYPE, 
                                CRS.%s AS CRS_ID, CRS.%s AS CRS_CAPACITY, CRS.%s AS SCHEDULE, CRS.%s AS TERM, ROOM.%s AS ROOM_ID, ROOM.%s AS ROOM_CAPACITY,
                                TKN.%s AS GRADE, TKN.%s AS APPROVED FROM %s.%s USR, %s.%s STD, %s.%s SUB, %s.%s CRS, %s.%s ROOM, %s.%s TKN, %s.%s PRG WHERE TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = CRS.%s AND TKN.%s = CRS.%s AND CRS.%s = SUB.%s AND CRS.%s = ROOM.%s AND STD.%s = USR.%s AND STD.%s = PRG.%s AND STD.%s = PRG.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_NAME,
                       TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
                       TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::COURSE_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_CAPACITY,
                       TableDefinition::COURSE_TABLE_FIELD_SCHEDULE,
                       TableDefinition::COURSE_TABLE_FIELD_TERM,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKENCOURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new TakenCourse(
                new Student(
                    new User(
                        oci_result($stmt, 'USER_ID'),
                        oci_result($stmt, 'USER_NAME'),
                        oci_result($stmt, 'EMAIL'),
                        oci_result($stmt, 'PASSWORD'),
                        oci_result($stmt, 'BIRTH_DATE')),
                    new Programme(
                        oci_result($stmt, 'PROGRAMME_NAME'),
                        oci_result($stmt, 'PROGRAMME_TYPE'),
                        oci_result($stmt, 'NO_TERMS')),
                    oci_result($stmt, 'START_TERM')),
                new Course(
                    new Subject(
                        oci_result($stmt, 'SUBJECT_ID'),
                        oci_result($stmt, 'SUBJECT_NAME'),
                        oci_result($stmt, 'APPROVAL'),
                        oci_result($stmt, 'CREDIT'),
                        oci_result($stmt, 'TYPE')),
                    oci_result($stmt, 'CRS_ID'),
                    oci_result($stmt, 'CRS_CAPACITY'),
                    oci_result($stmt, 'SCHEDULE'),
                    oci_result($stmt, 'TERM'),
                    new Room(
                        oci_result($stmt, 'ROOM_ID'),
                        oci_result($stmt, 'ROOM_CAPACITY')
                    )),
                oci_result($stmt, 'GRADE'),
                oci_result($stmt, 'APPROVED')
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ITakenCourse $model): array {
        $res = array();
        $crits = array();

        $sql = sprintf("SELECT USR.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                TKN.%s AS PROGRAMME_NAME, TKN.%s AS PROGRAMME_TYPE, STD.%s AS START_TERM, PRG.%s AS NO_TERMS,
                                TKN.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS TYPE, 
                                CRS.%s AS CRS_ID, CRS.%s AS CRS_CAPACITY, CRS.%s AS SCHEDULE, CRS.%s AS TERM, ROOM.%s AS ROOM_ID, ROOM.%s AS ROOM_CAPACITY,
                                TKN.%s AS GRADE, TKN.%s AS APPROVED FROM %s.%s USR, %s.%s STD, %s.%s SUB, %s.%s CRS, %s.%s ROOM, %s.%s TKN, %s.%s PRG WHERE TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = CRS.%s AND TKN.%s = CRS.%s AND CRS.%s = SUB.%s AND CRS.%s = ROOM.%s AND STD.%s = USR.%s AND STD.%s = PRG.%s AND STD.%s = PRG.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_NAME,
                       TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
                       TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::COURSE_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_CAPACITY,
                       TableDefinition::COURSE_TABLE_FIELD_SCHEDULE,
                       TableDefinition::COURSE_TABLE_FIELD_TERM,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKENCOURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE);

        $user = $model->getStudent();
        $course = $model->getCourse();

        if (isset($user) && $user->getUser() !== null && $user->getUser()->getId() !== null) {
            $crits[] = TableDefinition::USER_TABLE . "." . TableDefinition::USER_TABLE_FIELD_ID . " LIKE :userId";
            $userId = $model->getStudent()->getUser()->getId();
        }
        if (isset($course) && $course->getSubject() !== null && $course->getSubject()->getId() !== null) {
            $crits[] = TableDefinition::COURSE_TABLE . '.' . TableDefinition::COURSE_TABLE_FIELD_ID . " LIKE :courseId AND "
                . TableDefinition::COURSE_TABLE . '.' . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";
            $courseId = $course->getId();
            $subjectId = $course->getSubject()->getId();
        }

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($user) && isset($userId) && !oci_bind_by_name($stmt, ':userId', $userId, -1))
            throw new DataAccessException('bind user id ' . json_encode(oci_error($stmt)));
        if (isset($course) && isset($courseId) && !oci_bind_by_name($stmt, ':courseId', $courseId, -1))
            throw new DataAccessException('bind course id ' . json_encode(oci_error($stmt)));
        if (isset($course) && isset($subjectId) && !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
            throw new DataAccessException('bind subject id ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new TakenCourse(
                new Student(
                    new User(
                        oci_result($stmt, 'USER_ID'),
                        oci_result($stmt, 'USER_NAME'),
                        oci_result($stmt, 'EMAIL'),
                        oci_result($stmt, 'PASSWORD'),
                        oci_result($stmt, 'BIRTH_DATE')),
                    new Programme(
                        oci_result($stmt, 'PROGRAMME_NAME'),
                        oci_result($stmt, 'PROGRAMME_TYPE'),
                        oci_result($stmt, 'NO_TERMS')),
                    oci_result($stmt, 'START_TERM')),
                new Course(
                    new Subject(
                        oci_result($stmt, 'SUBJECT_ID'),
                        oci_result($stmt, 'SUBJECT_NAME'),
                        oci_result($stmt, 'APPROVAL'),
                        oci_result($stmt, 'CREDIT'),
                        oci_result($stmt, 'TYPE')),
                    oci_result($stmt, 'CRS_ID'),
                    oci_result($stmt, 'CRS_CAPACITY'),
                    oci_result($stmt, 'SCHEDULE'),
                    oci_result($stmt, 'TERM'),
                    new Room(
                        oci_result($stmt, 'ROOM_ID'),
                        oci_result($stmt, 'ROOM_CAPACITY')
                    )),
                oci_result($stmt, 'GRADE'),
                oci_result($stmt, 'APPROVED')
            );
        }

        return $res;
    }
    //endregion
}
