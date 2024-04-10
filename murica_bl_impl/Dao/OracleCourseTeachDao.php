<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ICourseTeachDao;
use murica_bl\Dto\ICourseTeach;
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

    //region IUserDao members
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getUser()->getId();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getSubject()->getId();


        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':courseId', $courseId, -1) ||
            !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new CourseTeach($model->getUser(), $model->getCourse(), $model->getSubject()))[0];
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getUser()->getId();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getSubject()->getId();

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new CourseTeach( new User(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'EMAIL'),
                oci_result($stmt, 'PASSWORD'),
                oci_result($stmt, 'BIRTH_DATE')),
                new Course(
                    new Subject(
                        oci_result($stmt, 'SUBJECT_ID'),
                        oci_result($stmt, 'NAME'),
                        oci_result($stmt, 'APPROVAL'),
                        oci_result($stmt, 'CREDIT'),
                        oci_result($stmt, 'TYPE')),
                    oci_result($stmt, 'ID'),
                    oci_result($stmt, 'CRS_CAPACITY'),
                    oci_result($stmt, 'SCHEDULE'),
                    oci_result($stmt, 'TERM'),
                    new Room(
                        oci_result($stmt, 'ROOM_ID'),
                        oci_result($stmt, 'ROOM_CAPACITY')))
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ICourseTeach $model): array {
        $res = array();
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

        $userId = $model->getUser()->getId();
        $courseId = $model->getCourse()->getId();
        $subjectId = $model->getSubject()->getId();

        if (isset($userId)) $crits[] = TableDefinition::COURSETEACH_TABLE_FIELD_USER_ID . " LIKE :userId";
        if (isset($courseId)) $crits[] = TableDefinition::COURSETEACH_TABLE_FIELD_COURSE_ID . " LIKE :courseId";
        if (isset($subjectId)) $crits[] = TableDefinition::COURSETEACH_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($userId) && !oci_bind_by_name($stmt, ':userId', $userId, -1))
            throw new DataAccessException('bind userId ' . json_encode(oci_error($stmt)));
        if (isset($courseId) && !oci_bind_by_name($stmt, ':courseId', $courseId, -1))
            throw new DataAccessException('bind courseId ' . json_encode(oci_error($stmt)));
        if (isset($subjectId) && !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
            throw new DataAccessException('bind subjectId ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new CourseTeach( new User(
                      oci_result($stmt, 'ID'),
                      oci_result($stmt, 'NAME'),
                      oci_result($stmt, 'EMAIL'),
                      oci_result($stmt, 'PASSWORD'),
                      oci_result($stmt, 'BIRTH_DATE')),
                  new Course(
                      new Subject(
                          oci_result($stmt, 'SUBJECT_ID'),
                          oci_result($stmt, 'NAME'),
                          oci_result($stmt, 'APPROVAL'),
                          oci_result($stmt, 'CREDIT'),
                          oci_result($stmt, 'TYPE')),
                      oci_result($stmt, 'ID'),
                      oci_result($stmt, 'CRS_CAPACITY'),
                      oci_result($stmt, 'SCHEDULE'),
                      oci_result($stmt, 'TERM'),
                      new Room(
                          oci_result($stmt, 'ROOM_ID'),
                          oci_result($stmt, 'ROOM_CAPACITY')))
            );
        }

        return $res;
    }
    //endregion
}
