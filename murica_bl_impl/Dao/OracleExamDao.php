<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IExamDao;
use murica_bl\Dto\IExam;
use murica_bl\Orm\Exception\OciException;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Exam;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleExamDao implements IExamDao {
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
    public function create(IExam $model): IExam {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s, %s) VALUES (:subjectId, :id, TO_DATE(:startTime, 'YYYY-MM-DD HH24:MI'), TO_DATE(:endTime, 'YYYY-MM-DD HH24:MI'), :teacherId, :roomId)",
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_START_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_END_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID);

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $startTime = $model->getStartTime();
        $endTime = $model->getEndTime();
        $teacherId = $model->getTeacher()->getId();
        $roomId = $model->getRoom()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':subjectId', $subjectId)
                ->bind(':id', $id)
                ->bind(':startTime', $startTime)
                ->bind(':endTime', $endTime)
                ->bind(':teacherId', $teacherId)
                ->bind(':roomId', $roomId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create exam', $e);
        }

        return $this->findByCrit(new Exam(new Subject($subjectId), $model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(IExam $model): IExam {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :teacherId, %s = :roomId WHERE %s = :subjectId AND %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID);

        $subjectId = $model->getSubject()->getId();
        $id = $model->getId();
        $startTime = $model->getStartTime();
        $endTime = $model->getEndTime();
        $teacherId = $model->getTeacher()->getId();
        $roomId = $model->getRoom()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':subjectId', $subjectId)
                ->bind(':id', $id)
                ->bind(':startTime', $startTime)
                ->bind(':endTime', $endTime)
                ->bind(':teacherId', $teacherId)
                ->bind(':roomId', $roomId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to update exam', $e);
        }

        return $this->findByCrit(new Exam($model->getSubject(), $model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(IExam $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :subjectId AND %s = :id",
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID);

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
            throw new DataAccessException('Failed to delete exam', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $res = array();

        $sql = "SELECT
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . " AS EXAM_ID,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " AS SUBJECT_ID,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS SUBJECT_APPROVAL,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS SUBJECT_CREDIT,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS SUBJECT_TYPE,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ",'YYYY-MM-DD HH:MI') AS START_TIME,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ",'YYYY-MM-DD HH:MI') AS END_TIME,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " AS TEACHER_ID,
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS TEACHER_NAME,
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS EMAIL,
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS PASSWORD,
            TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",'YYYY-MM-DD') AS BIRTH_DATE,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " AS ROOM_ID,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS CAPACITY, 
            COUNT(TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID. ") AS NO_STUDENTS
        FROM
            " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::EXAM_TABLE . " EXAM
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
            LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKEN_EXAM_TABLE . " TKN_EXAM
            ON TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . " AND 
            TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . "
        GROUP BY
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . ",
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

        try {
            $exams = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query exams', $e);
        }

        return $this->fetchExams($exams);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IExam $model): array {
        $crits = array();

        $sql = "SELECT
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . " AS EXAM_ID,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " AS SUBJECT_ID,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS SUBJECT_APPROVAL,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS SUBJECT_CREDIT,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS SUBJECT_TYPE,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ",'YYYY-MM-DD HH:MI') AS START_TIME,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ",'YYYY-MM-DD HH:MI') AS END_TIME,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " AS TEACHER_ID,
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS TEACHER_NAME,
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS EMAIL,
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS PASSWORD,
            TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",'YYYY-MM-DD') AS BIRTH_DATE,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " AS ROOM_ID,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS CAPACITY, 
            COUNT(TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . ") AS NO_STUDENTS
        FROM
            " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::EXAM_TABLE . " EXAM
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
            LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKEN_EXAM_TABLE . " TKN_EXAM
            ON TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . " AND 
            TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID;

        $subject = $model->getSubject();
        $id = $model->getId();
        $startTime = $model->getStartTime();
        $endTime = $model->getEndTime();
        $teacher = $model->getTeacher();
        $room = $model->getRoom();

        if (isset($subject) && $subject->getId() !== null) {
            $crits[] = " EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " LIKE :subjectId";
            $subjectId = $subject->getId();
        }
        if (isset($id)) $crits[] = " EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . " = :id";
        if (isset($startTime)) $crits[] = " EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . " LIKE :startTime";
        if (isset($endTime)) $crits[] = " EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . " LIKE :endTime";
        if (isset($teacher) && $teacher->getId() !== null) {
            $crits[] = " EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " LIKE :teacherId";
            $teacherId = $teacher->getId();
        }
        if (isset($room) && $room->getId() !== null) {
            $crits[] = " EXAM." . TableDefinition::COURSE_TABLE_FIELD_ROOM_ID . " LIKE :roomId";
            $roomId = $room->getId();
        }

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        $sql .= " GROUP BY
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . ",
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($subjectId)) $stmt->bind(':subjectId', $subjectId);
            if (isset($id)) $stmt->bind(':id', $id);
            if (isset($startTime)) $stmt->bind(':startTime', $startTime);
            if (isset($endTime)) $stmt->bind(':endTime', $endTime);
            if (isset($teacherId)) $stmt->bind(':teacherId', $teacherId);
            if (isset($roomId)) $stmt->bind(':roomId', $roomId);

            $exams = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query exams', $e);
        }

        return $this->fetchExams($exams);
    }
    //endregion

    private function fetchExams(array $exams): array {
        $res = array();

        foreach ($exams as $exam) {
            $res[] = new Exam(
                new Subject(
                    $exam['SUBJECT_ID'],
                    $exam['SUBJECT_NAME'],
                    $exam['SUBJECT_APPROVAL'],
                    $exam['SUBJECT_CREDIT'],
                    $exam['SUBJECT_TYPE']),
                $exam['EXAM_ID'],
                $exam['START_TIME'],
                $exam['END_TIME'],
                $exam['NO_STUDENTS'],
                new User(
                    $exam['TEACHER_ID'],
                    $exam['TEACHER_NAME'],
                    $exam['EMAIL'],
                    $exam['PASSWORD'],
                    $exam['BIRTH_DATE']),
                new Room(
                    $exam['ROOM_ID'],
                    $exam['CAPACITY'])
            );
        }

        return $res;
    }
}
