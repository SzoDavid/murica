<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ITakenExamDao;
use murica_bl\Dto\ITakenExam;
use murica_bl\Orm\Exception\OciException;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Exam;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\TakenExam;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleTakenExamDao implements ITakenExamDao {
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
    public function create(ITakenExam $model): ITakenExam {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s) VALUES (:userId, :programmeName, :programmeType, :examId, :subjectId)",
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID);
        
        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $examId = $model->getExam()->getId();
        $subjectId = $model->getExam()->getSubject()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $userId)
                ->bind(':programmeName', $programmeName)
                ->bind(':programmeType', $programmeType)
                ->bind(':examId', $examId)
                ->bind(':subjectId', $subjectId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create taken exam', $e);
        }

        return $model;
        //return $this->findByCrit(new TakenExam($model->getStudent(), $model->getExam()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(ITakenExam $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :userId AND %s = :programmeName AND %s = :programmeType AND %s = :examId AND %s = :subjectId",
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID);

        $userId = $model->getStudent()->getUser()->getId();
        $programmeName = $model->getStudent()->getProgramme()->getName();
        $programmeType = $model->getStudent()->getProgramme()->getType();
        $examId = $model->getExam()->getId();
        $subjectId = $model->getExam()->getSubject()->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $userId)
                ->bind(':programmeName', $programmeName)
                ->bind(':programmeType', $programmeType)
                ->bind(':examId', $examId)
                ->bind(':subjectId', $subjectId)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete taken exam', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $sql = "SELECT
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . " AS USER_ID,
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS USER_NAME,
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS USER_EMAIL,
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS USER_PASSWORD,
            TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 'YYYY-MM-DD') AS USER_BIRTH_DATE,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME . " AS PROGRAMME_NAME,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE . " AS PROGRAMME_TYPE,
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . " AS NO_TERMS,
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . " AS START_TERM,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " AS EXAM_ID,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " AS SUBJECT_ID,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ", 'YYYY-MM-DD HH24:MI') AS START_TIME,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ", 'YYYY-MM-DD HH24:MI') AS END_TIME,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " AS TEACHER_ID,
            TCHR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS TEACHER_NAME,
            TCHR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS TEACHER_EMAIL,
            TCHR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS TEACHER_PASSWORD,
            TO_CHAR(TCHR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 'YYYY-MM-DD') AS TEACHER_BIRTH_DATE,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . " AS SUBJECT_ID,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS SUBJECT_TYPE,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " AS ROOM_ID,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS CAPACITY, 
            COUNT(TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID. ") AS NO_STUDENTS
        FROM
            " . $this->configService->getTableOwner() . "." . TableDefinition::TAKEN_EXAM_TABLE . " TKN
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::STUDENT_TABLE . " STD
            ON TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " AND 
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " AND 
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::PROGRAMME_TABLE . " PRG
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NAME . " AND
            STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::EXAM_TABLE . " EXAM
            ON TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . " AND
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " TCHR
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " = TCHR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
            LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKEN_EXAM_TABLE . " TKN_EXAM
            ON TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " AND 
            TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . "
        GROUP BY 
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . ",
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE . ",
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . ",
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

        try {
            $takenExams = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query taken exams', $e);
        }

        return $this->fetchTakenExams($takenExams);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ITakenExam $model): array {
        $crits = array();

        $sql = "SELECT
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . " AS USER_ID,
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS USER_NAME,
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS USER_EMAIL,
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS USER_PASSWORD,
            TO_CHAR(USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 'YYYY-MM-DD') AS USER_BIRTH_DATE,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME . " AS PROGRAMME_NAME,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE . " AS PROGRAMME_TYPE,
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . " AS NO_TERMS,
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . " AS START_TERM,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " AS EXAM_ID,
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " AS SUBJECT_ID,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ", 'YYYY-MM-DD HH24:MI') AS START_TIME,
            TO_CHAR(EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ", 'YYYY-MM-DD HH24:MI') AS END_TIME,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " AS TEACHER_ID,
            TCHR." . TableDefinition::USER_TABLE_FIELD_NAME . " AS TEACHER_NAME,
            TCHR." . TableDefinition::USER_TABLE_FIELD_EMAIL . " AS TEACHER_EMAIL,
            TCHR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . " AS TEACHER_PASSWORD,
            TO_CHAR(TCHR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ", 'YYYY-MM-DD') AS TEACHER_BIRTH_DATE,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . " AS SUBJECT_ID,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . " AS SUBJECT_NAME,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . " AS APPROVAL,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . " AS CREDIT,
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " AS SUBJECT_TYPE,
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " AS ROOM_ID,
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " AS CAPACITY, 
            COUNT(TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID. ") AS NO_STUDENTS
        FROM
            " . $this->configService->getTableOwner() . "." . TableDefinition::TAKEN_EXAM_TABLE . " TKN
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::STUDENT_TABLE . " STD
            ON TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " AND 
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " AND 
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE . " = STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " USR
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " = USR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::PROGRAMME_TABLE . " PRG
            ON STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NAME . " AND
            STD." . TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . " = PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_TYPE . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::EXAM_TABLE . " EXAM
            ON TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID . " AND
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::USER_TABLE . " TCHR
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . " = TCHR." . TableDefinition::USER_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::SUBJECT_TABLE . " SUB
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " = SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . "
            JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::ROOM_TABLE . " ROOM
            ON EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . " = ROOM." . TableDefinition::ROOM_TABLE_FIELD_ID . "
            LEFT JOIN " . $this->configService->getTableOwner() . "." . TableDefinition::TAKEN_EXAM_TABLE . " TKN_EXAM
            ON TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID . " AND 
            TKN_EXAM." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " = EXAM." . TableDefinition::EXAM_TABLE_FIELD_ID;

        $student = $model->getStudent();
        $exam = $model->getExam();

        if (isset($student) && $student->getUser() !== null && $student->getUser()->getId() !== null) {
            $crits[] = "TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . " LIKE :userId";
            $userId = $model->getStudent()->getUser()->getId();
        }
        if (isset($exam) && $exam->getId() !== null && $exam->getSubject() !== null) {
            $crits[] = "TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . " LIKE :examId";
            $examId = $model->getExam()->getId();
        }

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        $sql .= " GROUP BY 
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID . ",
            USR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            USR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            USR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            USR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE . ",
            PRG." . TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . ",
            STD." . TableDefinition::STUDENT_TABLE_FIELD_START_TERM . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID . ",
            TKN." . TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_START_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_END_TIME . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_NAME . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_EMAIL . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_PASSWORD . ",
            TCHR." . TableDefinition::USER_TABLE_FIELD_BIRTH_DATE . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_ID . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_NAME . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_CREDIT . ",
            SUB." . TableDefinition::SUBJECT_TABLE_FIELD_TYPE . ",
            EXAM." . TableDefinition::EXAM_TABLE_FIELD_ROOM_ID . ",
            ROOM." . TableDefinition::ROOM_TABLE_FIELD_CAPACITY;

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($userId)) $stmt->bind(':userId', $userId);
            if (isset($examId)) $stmt->bind(':examId', $examId);

            $takenExams = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query taken exams', $e);
        }

        return $this->fetchTakenExams($takenExams);
    }

    private function fetchTakenExams(array $takenExams): array {
        $res = array();

        foreach ($takenExams as $exam) {
            $res[] = new TakenExam(
                new Student(
                    new User(
                        $exam['USER_ID'],
                        $exam['USER_NAME'],
                        $exam['USER_EMAIL'],
                        $exam['USER_PASSWORD'],
                        $exam['USER_BIRTH_DATE']),
                    new Programme(
                        $exam['PROGRAMME_NAME'],
                        $exam['PROGRAMME_TYPE'],
                        $exam['NO_TERMS']),
                    $exam['START_TERM']),
                new Exam(
                    new Subject(
                        $exam['SUBJECT_ID'],
                        $exam['SUBJECT_NAME'],
                        $exam['APPROVAL'],
                        $exam['CREDIT'],
                        $exam['SUBJECT_TYPE']),
                    $exam['EXAM_ID'],
                    $exam['START_TIME'],
                    $exam['END_TIME'],
                    $exam['NO_STUDENTS'],
                    new User(
                        $exam['TEACHER_ID'],
                        $exam['TEACHER_NAME'],
                        $exam['TEACHER_EMAIL'],
                        $exam['TEACHER_PASSWORD'],
                        $exam['TEACHER_BIRTH_DATE']),
                    new Room(
                        $exam['ROOM_ID'],
                        $exam['CAPACITY'])
                ));
        }

        return $res;
    }
    //endregion
}
