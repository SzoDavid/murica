<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dto\IStudent;
use murica_bl\Orm\Exception\OciException;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\User;
use Override;

class OracleStudentDao implements IStudentDao {
    //region Properties
    private OracleDataSource $dataSource;
    private IDataSourceConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(OracleDataSource $dataSource, IDataSourceConfigService $configService) {
        $this->dataSource = $dataSource;
        $this->configService = $configService;
    }
    //endregion

    //region IStudentDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(IStudent $model): IStudent {
        $model->validate();
        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s) VALUES (:userId, :programmeName, :programmeType, :startTerm)",
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM
        );

        $id = $model->getUser()->getId();
        $name = $model->getProgramme()->getName();
        $type = $model->getProgramme()->getType();
        $startTerm = $model->getStartTerm();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $id)
                ->bind(':programmeName', $name)
                ->bind(':programmeType', $type)
                ->bind(':startTerm', $startTerm)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create student', $e);
        }

        return $this->findByCrit(new Student($model->getUser(), $model->getProgramme()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(IStudent $model): IStudent {
        $model->validate();
        $sql = sprintf("UPDATE %s.%s SET %s = :programmeName, %s = :programmeType, %s = :startTerm WHERE %s = :userId AND %s = :programmeName AND %s = :programmeType",
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE
        );

        $id = $model->getUser()->getId();
        $name = $model->getProgramme()->getName();
        $type = $model->getProgramme()->getType();
        $startTerm = $model->getStartTerm();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $id)
                ->bind(':programmeName', $name)
                ->bind(':programmeType', $type)
                ->bind(':startTerm', $startTerm)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to update student', $e);
        }

        return $this->findByCrit(new Student($model->getUser(), $model->getProgramme()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(IStudent $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :userId AND %s = :programmeName AND %s = :programmeType",
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE
        );

        $id = $model->getUser()->getId();
        $name = $model->getProgramme()->getName();
        $type = $model->getProgramme()->getType();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $id)
                ->bind(':programmeName', $name)
                ->bind(':programmeType', $type)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete student', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $sql = sprintf("SELECT USR.%s AS ID, USR.%s AS NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                STD.%s AS PROGRAMME_NAME, STD.%s AS PROGRAMME_TYPE, STD.%s AS START_TERM,
                                PRG.%s AS NO_TERMS FROM %s.%s USR, %s.%s STD, %s.%s PRG WHERE USR.%s = STD.%s AND STD.%s = PRG.%s AND STD.%s = PRG.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE
        );

        try {
            $students = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query students', $e);
        }

        return $this->fetchStudents($students);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IStudent $model): array {
        $crits = array();

        $sql = sprintf("SELECT USR.%s AS ID, USR.%s AS NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE,
                                STD.%s AS PROGRAMME_NAME, STD.%s AS PROGRAMME_TYPE, STD.%s AS START_TERM,
                                PRG.%s AS NO_TERMS FROM %s.%s USR, %s.%s STD, %s.%s PRG WHERE USR.%s = STD.%s AND STD.%s = PRG.%s AND STD.%s = PRG.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::STUDENT_TABLE_FIELD_START_TERM,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::STUDENT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_USER_ID,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE
        );

        $user = $model->getUser();
        $programme = $model->getProgramme();
        $startTerm = $model->getStartTerm();

        if (isset($user) && $user->getId() !== null) {
            $crits[] = TableDefinition::STUDENT_TABLE_FIELD_USER_ID . " LIKE :userId";
            $userId = $user->getId();
        }
        if (isset($programme) && $programme->getName() !== null && $programme->getType() !== null) {
            $crits[] = TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_NAME . " LIKE :programmeName";
            $crits[] = TableDefinition::STUDENT_TABLE_FIELD_PROGRAMME_TYPE . " LIKE :programmeType";
            $programmeName = $model->getProgramme()->getName();
            $programmeType = $model->getProgramme()->getType();
        }
        if (isset($startTerm)) $crits[] = TableDefinition::STUDENT_TABLE_FIELD_START_TERM . " LIKE :startTerm";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($userId)) $stmt->bind(':userId', $userId);
            if (isset($programmeName)) $stmt->bind(':programmeName', $programmeName);
            if (isset($programmeType)) $stmt->bind(':programmeType', $programmeType);
            if (isset($startTerm)) $stmt->bind(':startTerm', $startTerm);

            $students = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query students', $e);
        }

        return $this->fetchStudents($students);
    }

    public function calculateKi(IStudent $model): string {
        $userId = $model->getUser()->getId();
        $programmeName = $model->getProgramme()->getName();
        $programmeType = $model->getProgramme()->getType();

        try {
            $this->dataSource->getConnection()
                ->query("BEGIN :res := calculate_ki(:userId, :programmeName, :programmeType); END;")
                ->bind(':res', $res, 200)
                ->bind(':userId', $userId)
                ->bind(':programmeName', $programmeName)
                ->bind(':programmeType', $programmeType)
                ->execute(OCI_DEFAULT)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to calculate ki', $e);
        }

        return $res;
    }

    public function calculateKki(IStudent $model): string {

        $sql = "BEGIN :res := calculate_kki(:userId, :programmeName, :programmeType); END;";

        $user = $model->getUser();
        $programme = $model->getProgramme();

        try {
            $this->dataSource->getConnection()
                ->query( "BEGIN :res := calculate_kki(:userId, :programmeName, :programmeType); END;")
                ->bind(':res', $res, 200)
                ->bind(':userId', $userId)
                ->bind(':programmeName', $programmeName)
                ->bind(':programmeType', $programmeType)
                ->execute(OCI_DEFAULT)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to calculate ki', $e);
        }

        return $res;
    }
    //endregion

    private function fetchStudents(array $students): array {
        $res = array();

        foreach ($students as $student) {
            $res[] = new Student(
                new User(
                    $student['ID'],
                    $student['NAME'],
                    $student['EMAIL'],
                    $student['PASSWORD'],
                    $student['BIRTH_DATE']),
                new Programme(
                    $student['PROGRAMME_NAME'],
                    $student['PROGRAMME_TYPE'],
                    $student['NO_TERMS']),
                $student['START_TERM']);
        }

        return $res;
    }

}



