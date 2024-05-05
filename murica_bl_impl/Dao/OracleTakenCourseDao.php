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
        $sql = "SELECT
            USR." . TableDefinition::USER_TABLE_FIELD_ID . " AS USER_ID,
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS USER_NAME,
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS EMAIL,
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS PASSWORD,
            TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 'YYYY-MM-DD') AS BIRTH_DATE,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME . " AS PROGRAMME_NAME,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE . " AS PROGRAMME_TYPE,
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . " AS START_TERM,
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . " AS NO_TERMS,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " AS SUBJECT_ID,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS TYPE,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . " AS CRS_ID,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . " AS CRS_CAPACITY,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . " AS SCHEDULE,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . " AS TERM,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . " AS ROOM_ID,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS ROOM_CAPACITY,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE . " AS GRADE,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED . " AS APPROVED,
            COUNT(TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . ") AS NO_STUDENTS
        FROM
            " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::STUDENT_TABLE . " STD
            ON TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_USER_ID . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . "
            AND TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . "
            AND TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::PROGRAMME_TABLE . " PRG
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NAME . "
            AND STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSE_TABLE . " CRS
            ON TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . "
            AND TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
            ON CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
            ON CRS." . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
            LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN_CRS
            ON TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " 
            AND TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS. " . TableDefinition::COURSE_TABLE_FIELD_ID . "
        GROUP BY
            USR." . TableDefinition::USER_TABLE_FIELD_ID . ",
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE . ",
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . ",
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED;

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
        $sql = "SELECT
            USR." . TableDefinition::USER_TABLE_FIELD_ID . " AS USER_ID,
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS USER_NAME,
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS EMAIL,
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS PASSWORD,
            TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 'YYYY-MM-DD') AS BIRTH_DATE,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME . " AS PROGRAMME_NAME,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE . " AS PROGRAMME_TYPE,
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . " AS START_TERM,
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . " AS NO_TERMS,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " AS SUBJECT_ID,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS TYPE,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . " AS CRS_ID,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . " AS CRS_CAPACITY,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . " AS SCHEDULE,
            CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . " AS TERM,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . " AS ROOM_ID,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS ROOM_CAPACITY,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE . " AS GRADE,
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED . " AS APPROVED,
            COUNT(TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . ") AS NO_STUDENTS
        FROM
            " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::STUDENT_TABLE . " STD
            ON TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_USER_ID . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . "
            AND TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . "
            AND TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::PROGRAMME_TABLE . " PRG
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NAME . "
            AND STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSE_TABLE . " CRS
            ON TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . "
            AND TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
            ON CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
            ON CRS." . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
            LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN_CRS
            ON TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " 
            AND TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS. " . TableDefinition::COURSE_TABLE_FIELD_ID;

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
            $sql .= " WHERE " . implode(" AND ", $crits);

        $sql .= " GROUP BY
            USR." . TableDefinition::USER_TABLE_FIELD_ID . ",
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_NAME . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_PROGRAMME_TYPE . ",
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . ",
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . ",
            CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_GRADE . ",
            TKN." . TableDefinition::TAKENCOURSE_TABLE_FIELD_APPROVED;

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
                    ),
                    $course['NO_STUDENTS']),
                $course['GRADE'],
                $course['APPROVED']
            );
        }

        return $res;
    }
}
