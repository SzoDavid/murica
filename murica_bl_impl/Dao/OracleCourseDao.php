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
        // TODO: this returns nothing
        //return $this->findByCrit(new Course($model->getSubject(), $model->getId()))[0];
        return $model;
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

        $sql = "SELECT 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . " AS SUBJECT_ID, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS NAME, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS TYPE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . " AS ID, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . " AS CRS_CAPACITY, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . " AS SCHEDULE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . " AS TERM, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . " AS ROOM_ID, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS ROOM_CAPACITY,
                    COUNT(TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . ") AS NO_PARTICIPANTS 
                FROM 
                    " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSE_TABLE . " CRS
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID ."
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
                    LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN_CRS
                    ON TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " AND 
                    TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS. " . TableDefinition::COURSE_TABLE_FIELD_ID . "
                GROUP BY
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . ", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . ", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

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

        $sql = "SELECT 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . " AS SUBJECT_ID, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS NAME, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT, 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS TYPE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . " AS ID, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . " AS CRS_CAPACITY, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . " AS SCHEDULE, 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . " AS TERM, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . " AS ROOM_ID, 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS ROOM_CAPACITY,
                    COUNT(TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . ") AS NO_PARTICIPANTS 
                FROM 
                    " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::COURSE_TABLE . " CRS
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID ."
                    JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
                    ON CRS." . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
                    LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKENCOURSE_TABLE . " TKN_CRS
                    ON TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_SUBJECT_ID . " = CRS." . TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID . " AND 
                    TKN_CRS." . TableDefinition::TAKENCOURSE_TABLE_FIELD_COURSE_ID . " = CRS. " . TableDefinition::COURSE_TABLE_FIELD_ID;

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
            $sql .= ' WHERE ' . implode(' AND ', $crits);

        $sql .= " GROUP BY
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ", 
                    SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_ID . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_CAPACITY . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_SCHEDULE . ", 
                    CRS." . TableDefinition::COURSE_TABLE_FIELD_TERM . ", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . ", 
                    ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

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
                    $course['ROOM_CAPACITY']),
                $course['NO_PARTICIPANTS']
            );
        }

        return $res;
    }
}
