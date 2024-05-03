<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ICourseDao;
use murica_bl\Dto\ICourse;
use murica_bl\Orm\Exception\OciException;
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

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $capacity = $model->getCapacity();
        $schedule = $model->getSchedule();
        $term = $model->getTerm();
        $roomId = $model->getRoom()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':subjectId', $subjectId)
                ->bind(':id', $id)
                ->bind(':capacity', $capacity)
                ->bind(':schedule', $schedule)
                ->bind(':term', $term)
                ->bind(':roomId', $roomId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $exception) {
            throw new DataAccessException('Failed to create Course', $exception);
        }

        return $this->findByCrit(new Course($model->getSubject(), $model->getId()))[0];
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

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $capacity = $model->getCapacity();
        $schedule = $model->getSchedule();
        $term = $model->getTerm();
        $roomId = $model->getRoom()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':subjectId', $subjectId)
                ->bind(':id', $id)
                ->bind(':capacity', $capacity)
                ->bind(':schedule', $schedule)
                ->bind(':term', $term)
                ->bind(':roomId', $roomId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $exception) {
            throw new DataAccessException('Failed to update Course', $exception);
        }

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


        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':subjectId', $subjectId)
                ->bind(':id', $id)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete Course', $e);
        }
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
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID);

        try {
            $courses = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query Courses', $e);
        }

        return $this->fetchCourses($courses);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ICourse $model): array {
        $crits = array();

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
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID);

        $id = $model->getId();
        $schedule = $model->getSchedule();
        $term = $model->getTerm();
        $subject = $model->getSubject();
        $room = $model->getRoom();

        if (isset($subject) && $subject->getId() !== null) {
            $crits[] = 'CRS.' . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . ' LIKE :subjectId';
            $subjectId = $subject->getId();
        }
        if (isset($id)) $crits[] = 'CRS.' . TableDefinition::COURSE_TABLE_FIELD_ID . ' = :id';
        if (isset($schedule)) $crits[] = TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . ' LIKE :schedule';
        if (isset($term)) $crits[] = TableDefinition::COURSE_TABLE_FIELD_TERM . ' LIKE :term';
        if (isset($room) && $room->getId() !== null) {
            $crits[] = 'ROOM.' . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . ' LIKE :roomId';
            $roomId = $room->getId();
        }

        if (!empty($crits))
            $sql .= ' AND ' . implode(' AND ', $crits);

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($subjectId)) $stmt->bind(':subjectId', $subjectId);
            if (isset($id)) $stmt->bind(':id', $id);
            if (isset($schedule)) $stmt->bind(':schedule', $schedule);
            if (isset($term)) $stmt->bind(':term', $term);
            if (isset($roomId)) $stmt->bind(':roomId', $roomId);

            $courses = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to execute query', $e);
        }

        return $this->fetchCourses($courses);
    }
    //endregion

    private function fetchCourses(array $courses): array {
        $res = array();

        foreach ($courses as $course) {
            $res[] = new Course(
                new Subject(
                    $course['SUBJECT_ID'],
                    $course['NAME'],
                    $course['APPROVAL'],
                    $course['CREDIT'],
                    $course['TYPE']),
                $course['ID'],
                $course['CRS_CAPACITY'],
                $course['SCHEDULE'],
                $course['TERM'],
                new Room(
                    $course['ROOM_ID'],
                    $course['ROOM_CAPACITY'])
            );
        }

        return $res;
    }
}
