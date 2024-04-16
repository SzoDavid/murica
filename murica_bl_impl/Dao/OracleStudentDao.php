<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dto\IStudent;
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_bind_by_name($stmt, ':userId', $IUser, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':startTerm', $startTerm, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));


        $userId = $model->getUser()->getId();
        $programmeName = $model->getProgramme()->getName();
        $programmeType = $model->getProgramme()->getType();
        $startTerm = $model->getStartTerm();

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1) ||
            !oci_bind_by_name($stmt, ':startTerm', $startTerm, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $userId = $model->getUser()->getId();
        $programmeName = $model->getProgramme()->getName();
        $programmeType = $model->getProgramme()->getType();

        if (!oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1) ||
            !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1))
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Student(
                new User(
                    oci_result($stmt, 'ID'),
                    oci_result($stmt, 'NAME'),
                    oci_result($stmt, 'EMAIL'),
                    oci_result($stmt, 'PASSWORD'),
                    oci_result($stmt, 'BIRTH_DATE')),
                new Programme(
                    oci_result($stmt, 'PROGRAMME_NAME'),
                    oci_result($stmt, 'PROGRAMME_TYPE'),
                    oci_result($stmt, 'NO_TERMS')),
                oci_result($stmt, 'START_TERM')
            );
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IStudent $model): array {
        $res = array();
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (isset($userId) && !oci_bind_by_name($stmt, ':userId', $userId, -1))
            throw new DataAccessException('bind userId ' . json_encode(oci_error($stmt)));
        if (isset($programmeName) && !oci_bind_by_name($stmt, ':programmeName', $programmeName, -1))
            throw new DataAccessException('bind programmeName ' . json_encode(oci_error($stmt)));
        if (isset($programmeType) && !oci_bind_by_name($stmt, ':programmeType', $programmeType, -1))
            throw new DataAccessException('bind programmeType' . json_encode(oci_error($stmt)));
        if (isset($startTerm) && !oci_bind_by_name($stmt, ':startTerm', $startTerm, -1))
            throw new DataAccessException('bind startTerm ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Student(
                new User(
                    oci_result($stmt, 'ID'),
                    oci_result($stmt, 'NAME'),
                    oci_result($stmt, 'EMAIL'),
                    oci_result($stmt, 'PASSWORD'),
                    oci_result($stmt, 'BIRTH_DATE')),
                new Programme(
                    oci_result($stmt, 'PROGRAMME_NAME'),
                    oci_result($stmt, 'PROGRAMME_TYPE'),
                    oci_result($stmt, 'NO_TERMS')),
                oci_result($stmt, 'START_TERM')
            );
        }

        return $res;
    }
    //endregion
}



