<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ICourseDao;
use murica_bl\Dto\ICourse;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Course;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleCourseDao implements ICourseDao {
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

    //region ICourseDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(ICourse $model): ICourse {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s, %s) VALUES (:subjectId, :id, :capacity, :schedule, :term, :roomId)",
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_CAPACITY,
                       TableDefinition::COURSE_TABLE_FIELD_SCHEDULE,
                       TableDefinition::COURSE_TABLE_FIELD_TERM,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $capacity = $model->getCapacity();
        $schedule = $model->getSchedule();
        $term = $model->getTerm();
        $roomId = $model->getRoom()->getId();

        if (!oci_bind_by_name($stmt, ':subjectId', $subjectId, -1) ||
            !oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':capacity', $capacity, -1) ||
            !oci_bind_by_name($stmt, ':schedule', $schedule, -1) ||
            !oci_bind_by_name($stmt, ':term', $term, -1) ||
            !oci_bind_by_name($stmt, ':roomId', $roomId, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Course($model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(ICourse $model): ICourse {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :capacity, %s = :schedule, %s = :term, %s = :roomId WHERE %s = :subjectId AND %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_CAPACITY,
                       TableDefinition::COURSE_TABLE_FIELD_SCHEDULE,
                       TableDefinition::COURSE_TABLE_FIELD_TERM,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $capacity = $model->getCapacity();
        $schedule = $model->getSchedule();
        $term = $model->getTerm();
        $roomId = $model->getRoom()->getId();

        if (!oci_bind_by_name($stmt, ':subjectId', $subjectId, -1) ||
            !oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':capacity', $capacity, -1) ||
            !oci_bind_by_name($stmt, ':schedule', $schedule, -1) ||
            !oci_bind_by_name($stmt, ':term', $term, -1) ||
            !oci_bind_by_name($stmt, ':roomId', $roomId, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Course($model->getSubject(), $model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(ICourse $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :subjectId AND %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ID);

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

        $sql = sprintf("SELECT SUB.%s AS SUBJECT_ID, SUB.%s AS NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS TYPE, CRS.%s AS ID, CRS.%s AS CRS_CAPACITY, CRS.%s AS SCHEDULE, CRS.%s AS TERM, ROOM.%s AS ROOM_ID, ROOM.%s AS ROOM_CAPACITY FROM %s.%s SUB, %s.%s CRS, %s.%s ROOM WHERE CRS.%s = SUB.%s AND CRS.%s = ROOM.%s",
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
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Course( new Subject(
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
                    oci_result($stmt, 'ROOM_CAPACITY'))
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ICourse $model): array {
        $res = array();
        $crits = array();

        $sql = sprintf("SELECT SUB.%s AS SUBJECT_ID, SUB.%s AS NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS TYPE, CRS.%s AS ID, %s AS CRS_CAPACITY, CRS.%s AS SCHEDULE, CRS.%s AS TERM, ROOM.%s AS ROOM_ID, ROOM.%s AS ROOM_CAPACITY FROM %s.%s SUB, %s.%s CRS, %s.%s ROOM WHERE CRS.%s = SUB.%s AND CRS.%s = ROOM.%s",
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
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID);


        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $schedule = $model->getSchedule();
        $term = $model->getTerm();
        $roomId = $model->getRoom()->getId();


        if (isset($subjectId)) $crits[] = TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";
        if (isset($id)) $crits[] = TableDefinition::COURSE_TABLE_FIELD_ID . " LIKE :id";
        if (isset($schedule)) $crits[] = TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . " LIKE :schedule";
        if (isset($term)) $crits[] = TableDefinition::COURSE_TABLE_FIELD_TERM . " LIKE :term";
        if (isset($roomId)) $crits[] = TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " LIKE :roomId";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($id) && !oci_bind_by_name($stmt, ':subjectId', $subjectId, -1))
            throw new DataAccessException('bind subjectId ' . json_encode(oci_error($stmt)));
        if (isset($id) && !oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));
        if (isset($name) && !oci_bind_by_name($stmt, ':schedule', $schedule, -1))
            throw new DataAccessException('bind schedule ' . json_encode(oci_error($stmt)));
        if (isset($email) && !oci_bind_by_name($stmt, ':term', $term, -1))
            throw new DataAccessException('bind term ' . json_encode(oci_error($stmt)));
        if (isset($id) && !oci_bind_by_name($stmt, ':roomId', $roomId, -1))
            throw new DataAccessException('bind roomId ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Course( new Subject(
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
                                     oci_result($stmt, 'ROOM_CAPACITY'))
            );
        }

        return $res;
    }
    //endregion
}
