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
        $sql = sprintf("SELECT TKN.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS USER_EMAIL, USR.%s AS USER_PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS USER_BIRTH_DATE, 
                               TKN.%s AS PROGRAMME_NAME, TKN.%s AS PROGRAMME_TYPE, PRG.%s AS NO_TERMS,
                               STD.%s AS START_TERM, TKN.%s AS EXAM_ID, TKN.%s AS SUBJECT_ID,
                               TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS START_TIME, TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS END_TIME, EXAM.%s AS TEACHER_ID,
                               TCHR.%s AS TEACHER_NAME, TCHR.%s AS TEACHER_EMAIL, TCHR.%s AS TEACHER_PASSWORD, TO_CHAR(TCHR.%s,'YYYY-MM-DD') AS TEACHER_BIRTH_DATE,
                               SUB.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS SUBJECT_TYPE, EXAM.%s AS ROOM_ID, ROOM.%s AS CAPACITY
                               FROM %s.%s USR, %s.%s EXAM, %s.%s TKN, %s.%s STD,
                               %s.%s PRG, %s.%s TCHR, %s.%s SUB, %s.%s ROOM
                               WHERE TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = USR.%s AND
                               TKN.%s = PRG.%s AND TKN.%s = PRG.%s AND TKN.%s = EXAM.%s AND TKN.%s = EXAM.%s AND EXAM.%s = SUB.%s AND EXAM.%s = TCHR.%s AND EXAM.%s = ROOM.%s;",
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_START_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_END_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_NAME,
                       TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
                       TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID
        );

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
        
        $sql = sprintf("SELECT TKN.%s AS USER_ID, USR.%s AS USER_NAME, USR.%s AS USER_EMAIL, USR.%s AS USER_PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS USER_BIRTH_DATE, 
                               TKN.%s AS PROGRAMME_NAME, TKN.%s AS PROGRAMME_TYPE, PRG.%s AS NO_TERMS,
                               STD.%s AS START_TERM, TKN.%s AS EXAM_ID, TKN.%s AS SUBJECT_ID,
                               TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS START_TIME, TO_CHAR(EXAM.%s,'YYYY-MM-DD HH:MI') AS END_TIME, EXAM.%s AS TEACHER_ID,
                               TCHR.%s AS TEACHER_NAME, TCHR.%s AS TEACHER_EMAIL, TCHR.%s AS TEACHER_PASSWORD, TO_CHAR(TCHR.%s,'YYYY-MM-DD') AS TEACHER_BIRTH_DATE,
                               SUB.%s AS SUBJECT_ID, SUB.%s AS SUBJECT_NAME, SUB.%s AS APPROVAL, SUB.%s AS CREDIT, SUB.%s AS SUBJECT_TYPE, EXAM.%s AS ROOM_ID, ROOM.%s AS CAPACITY
                               FROM %s.%s USR, %s.%s EXAM, %s.%s TKN, %s.%s STD,
                               %s.%s PRG, %s.%s TCHR, %s.%s SUB, %s.%s ROOM
                               WHERE TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = STD.%s AND TKN.%s = USR.%s AND
                               TKN.%s = PRG.%s AND TKN.%s = PRG.%s AND TKN.%s = EXAM.%s AND TKN.%s = EXAM.%s AND EXAM.%s = SUB.%s AND EXAM.%s = TCHR.%s AND EXAM.%s = ROOM.%s",
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_START_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_END_TIME,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_NAME,
                       TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
                       TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::TAKEN_EXAM_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_USER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_EXAM_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ID,
                       TableDefinition::TAKEN_EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::EXAM_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_TEACHER_ID,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::EXAM_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID
        );

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
            $sql .= " AND " . implode(" AND ", $crits);

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
                    0,
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
