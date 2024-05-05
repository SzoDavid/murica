<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ICourseTeachDao;
use murica_bl\Dto\ICourseTeach;
use murica_bl\Orm\Exception\OciException;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Course;
use murica_bl_impl\Dto\CourseTeach;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleCourseTeachDao implements ICourseTeachDao {
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

    //region ICourseTeachDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(ICourseTeach $model): ICourseTeach {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s) VALUES (:userId, :courseId, :subjectId)",
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSETEACH_TABLE,
                       TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID,
                       TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID,
                       TableDefinition::COURSETEACH_TABLE_FIELD_SUBJECT_ID);

        $userId = $model->getUser()->getId();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $userId)
                ->bind(':courseId', $courseId)
                ->bind(':subjectId', $subjectId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create courseTeach', $e);
        }

        return $this->findByCrit(new CourseTeach($model->getUser(), $model->getCourse()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(ICourseTeach $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :userId AND %s = :courseId AND %s = :subjectId",
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSETEACH_TABLE,
                       TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID,
                       TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID,
                       TableDefinition::COURSETEACH_TABLE_FIELD_SUBJECT_ID);

        $userId = $model->getUser()->getId();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getCourse()->getSubject()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $userId)
                ->bind(':courseId', $courseId)
                ->bind(':subjectId', $subjectId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete courseTeach', $e);
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
                    TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",'YYYY-MM-DD') AS BIRTH_DATE, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID ." AS SUBJECT_ID, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS TYPE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . " AS CRS_ID, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . " AS CRS_CAPACITY, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . " AS SCHEDULE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . " AS TERM, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID ." AS ROOM_ID, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY ." AS ROOM_CAPACITY, 
                    COUNT(TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID .") AS NO_STUDENTS
                FROM 
                    " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSETEACH_TABLE . " CRS_TCH
                    ON CRS_TCH." . TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID ."
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSE_TABLE . " CRS
                    ON CRS_TCH." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " AND
                    CRS_TCH." . TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . "
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " = ROOM." .TableDefinition::ROOM_TABLE_FIELD_ID . "
                    LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN_CRS 
                    ON TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " AND 
                    TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . "
                GROUP BY
                    USR." . TableDefinition::USER_TABLE_FIELD_ID . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_NAME . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID .", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . ", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID .", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

        try {
            $courseTeaches = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query courseTeaches', $e);
        }

        return $this->fetchCourseTeaches($courseTeaches);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ICourseTeach $model): array {
        $crits = array();

        $sql = "SELECT 
                    USR." . TableDefinition::USER_TABLE_FIELD_ID . " AS USER_ID, 
                    USR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS USER_NAME, 
                    USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS EMAIL, 
                    USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS PASSWORD, 
                    TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",'YYYY-MM-DD') AS BIRTH_DATE, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID ." AS SUBJECT_ID, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS TYPE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . " AS CRS_ID, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . " AS CRS_CAPACITY, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . " AS SCHEDULE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . " AS TERM, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID ." AS ROOM_ID, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY ." AS ROOM_CAPACITY, 
                    COUNT(TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID .") AS NO_STUDENTS
                FROM 
                    " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSETEACH_TABLE . " CRS_TCH
                    ON CRS_TCH." . TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID ."
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSE_TABLE . " CRS
                    ON CRS_TCH." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " AND
                    CRS_TCH." . TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . "
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " = ROOM." .TableDefinition::ROOM_TABLE_FIELD_ID . "
                    LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN_CRS 
                    ON TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " AND 
                    TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_ID;

        $user = $model->getUser();
        $course = $model->getCourse();

        if (isset($user) && $user->getId() !== null) {
            $crits[] = 'CRS_TCH.' . TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID . " LIKE :userId";
            $userId = $user->getId();
        }
        if (isset($course) && $course->getId() !== null && $course->getSubject() !== null && $course->getSubject()->getId() !== null) {
            $crits[] = 'CRS_TCH.' . TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID . " = :courseId";
            $crits[] = 'CRS_TCH.' . TableDefinition::COURSETEACH_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";
            $courseId = $course->getId();
            $subjectId = $course->getSubject()->getId();
        }

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        $sql .=" GROUP BY
                    USR." . TableDefinition::USER_TABLE_FIELD_ID . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_NAME . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ", 
                    USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID .", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . ", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID .", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($userId)) $stmt->bind(':userId', $userId);
            if (isset($courseId)) $stmt->bind(':courseId', $courseId);
            if (isset($subjectId)) $stmt->bind(':subjectId', $subjectId);

            $courseTeaches = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query admins', $e);
        }

        return $this->fetchCourseTeaches($courseTeaches);
    }
    //endregion

    private function fetchCourseTeaches(array $courseTeaches): array {
        $res = array();

        foreach ($courseTeaches as $courseTeach) {
            $res[] = new CourseTeach(
                new User(
                    $courseTeach['USER_ID'],
                    $courseTeach['USER_NAME'],
                    $courseTeach['EMAIL'],
                    $courseTeach['PASSWORD'],
                    $courseTeach['BIRTH_DATE']),
                new Course(
                    new Subject(
                        $courseTeach['SUBJECT_ID'],
                        $courseTeach['SUBJECT_NAME'],
                        $courseTeach['APPROVAL'],
                        $courseTeach['CREDIT'],
                        $courseTeach['TYPE']),
                    $courseTeach['CRS_ID'],
                    $courseTeach['CRS_CAPACITY'],
                    $courseTeach['SCHEDULE'],
                    $courseTeach['TERM'],
                    new Room(
                        $courseTeach['ROOM_ID'],
                        $courseTeach['ROOM_CAPACITY']),
                    $courseTeach['NO_STUDENTS']));
        }

        return $res;
    }
}
