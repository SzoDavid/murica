<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ITakenCourseDao;
use murica_bl\Dto\ITakenCourse;
use murica_bl\Orm\Exception\OciException;
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

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();
        $grade = $model->getGrade();
        $approved = $model->isApproved() ? 1 : 0;

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $userId)
                ->bind(':programmeName', $programmeName)
                ->bind(':programmeType', $programmeType)
                ->bind(':courseId', $courseId)
                ->bind(':subjectId', $subjectId)
                ->bind(':grade', $grade)
                ->bind(':approved', $approved)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create taken course', $e);
        }

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

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();
        $grade = $model->getGrade();
        $approved = $model->isApproved() ? 1 : 0;

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $userId)
                ->bind(':programmeName', $programmeName)
                ->bind(':programmeType', $programmeType)
                ->bind(':courseId', $courseId)
                ->bind(':subjectId', $subjectId)
                ->bind(':grade', $grade)
                ->bind(':approved', $approved)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to update taken course', $e);
        }

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

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $userId)
                ->bind(':programmeName', $programmeName)
                ->bind(':programmeType', $programmeType)
                ->bind(':courseId', $courseId)
                ->bind(':subjectId', $subjectId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete taken course', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
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

        try {
            $takenCourses = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query taken courses', $e);
        }

        return $this->fetchTakenCourses($takenCourses);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ITakenCourse $model): array {
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
            $crits[] = "USR." . TableDefinition::USER_TABLE_FIELD_ID . " LIKE :userId";
            $userId = $model->getStudent()->getUser()->getId();
        }
        if (isset($course) && $course->getSubject() !== null && $course->getSubject()->getId() !== null) {
            $crits[] = "CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . " = :courseId AND "
                . "CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";
            $courseId = $course->getId();
            $subjectId = $course->getSubject()->getId();
        }

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($userId)) $stmt->bind(':userId', $userId);
            if (isset($courseId)) $stmt->bind(':courseId', $courseId);
            if (isset($subjectId)) $stmt->bind(':subjectId', $subjectId);

            $takenCourses = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query subjects', $e);
        }

        return $this->fetchTakenCourses($takenCourses);
    }
    //endregion

    private function fetchTakenCourses(array $takenCourses): array {
        $res = array();

        foreach ($takenCourses as $course) {
            $res[] = new TakenCourse(
                new Student(
                    new User(
                        $course['USER_ID'],
                        $course['USER_NAME'],
                        $course['EMAIL'],
                        $course['PASSWORD'],
                        $course['BIRTH_DATE']),
                    new Programme(
                        $course['PROGRAMME_NAME'],
                        $course['PROGRAMME_TYPE'],
                        $course['NO_TERMS']),
                    $course['START_TERM']),
                new Course(
                    new Subject(
                        $course['SUBJECT_ID'],
                        $course['SUBJECT_NAME'],
                        $course['APPROVAL'],
                        $course['CREDIT'],
                        $course['TYPE']),
                    $course['CRS_ID'],
                    $course['CRS_CAPACITY'],
                    $course['SCHEDULE'],
                    $course['TERM'],
                    new Room(
                        $course['ROOM_ID'],
                        $course['ROOM_CAPACITY']
                    )),
                $course['GRADE'],
                $course['APPROVED']
            );
        }

        return $res;
    }
}
