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
                ->execute(OCI_COMMIT_ON_SUCCESS);
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
                ->execute(OCI_COMMIT_ON_SUCCESS);
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete courseTeach', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $sql = sprintf("SELECT USR.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE, SUB.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS TYPE, CRS.%s AS CRS_ID, CRS.%s AS CRS_CAPACITY, CRS.%s AS SCHEDULE, CRS.%s AS TERM, ROOM.%s AS ROOM_ID, ROOM.%s AS ROOM_CAPACITY FROM %s.%s USR, %s.%s CRS, %s.%s SUB, %s.%s ROOM, %s.%s CRSTCH WHERE CRSTCH.%s = USR.%s AND CRSTCH.%s = CRS.%s AND CRSTCH.%s = SUB.%s AND CRS.%s = SUB.%s AND CRS.%s = ROOM.%s",
           TableDefinition::USER_TABLE_FIELD_ID,
                   TableDefinition::USER_TABLE_FIELD_NAME,
                   TableDefinition::USER_TABLE_FIELD_EMAIL,
                   TableDefinition::USER_TABLE_FIELD_PASSWORD,
                   TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                   TableDefinition::SUBJECT_TABLE_FIELD_ID,
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
                   $this->configService->getTableOwner(),
                   TableDefinition::USER_TABLE,
                   $this->configService->getTableOwner(),
                   TableDefinition::COURSE_TABLE,
                   $this->configService->getTableOwner(),
                   TableDefinition::SUBJECT_TABLE,
                   $this->configService->getTableOwner(),
                   TableDefinition::ROOM_TABLE,
                   $this->configService->getTableOwner(),
                   TableDefinition::COURSETEACH_TABLE,
                   TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID,
                   TableDefinition::USER_TABLE_FIELD_ID,
                   TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID,
                   TableDefinition::COURSE_TABLE_FIELD_ID,
                   TableDefinition::COURSETEACH_TABLE_FIELD_SUBJECT_ID,
                   TableDefinition::SUBJECT_TABLE_FIELD_ID,
                   TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                   TableDefinition::SUBJECT_TABLE_FIELD_ID,
                   TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                   TableDefinition::ROOM_TABLE_FIELD_ID
        );

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

        $sql = sprintf("SELECT USR.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE, SUB.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS TYPE, CRS.%s AS CRS_ID, CRS.%s AS CRS_CAPACITY, CRS.%s AS SCHEDULE, CRS.%s AS TERM, ROOM.%s AS ROOM_ID, ROOM.%s AS ROOM_CAPACITY FROM %s.%s USR, %s.%s CRS, %s.%s SUB, %s.%s ROOM, %s.%s CRSTCH WHERE CRSTCH.%s = USR.%s AND CRSTCH.%s = CRS.%s AND CRSTCH.%s = SUB.%s AND CRS.%s = SUB.%s AND CRS.%s = ROOM.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
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
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSETEACH_TABLE,
                       TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ID,
                       TableDefinition::COURSETEACH_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID
        );

        $user = $model->getUser();
        $course = $model->getCourse();

        if (isset($user) && $user->getId() !== null) {
            $crits[] = 'CRSTCH.' . TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID . " LIKE :userId";
            $userId = $user->getId();
        }
        if (isset($course) && $course->getId() !== null && $course->getSubject() !== null && $course->getSubject()->getId() !== null) {
            $crits[] = 'CRSTCH.' . TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID . " = :courseId";
            $crits[] = 'CRSTCH.' . TableDefinition::COURSETEACH_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";
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
                        $courseTeach['ROOM_CAPACITY'])));
        }

        return $res;
    }
}
