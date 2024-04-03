<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dto\ISubject;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Subject;
use Override;

class OracleSubjectDao implements ISubjectDao
{

    //region Properties
    private OracleDataSource $dataSource;
    private IDataSourceConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(OracleDataSource $dataSource, IDataSourceConfigService $configService)
    {
        $this->dataSource = $dataSource;
        $this->configService = $configService;
    }
    //endregion

    //region ISubjectDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(ISubject $model): ISubject {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s, %s) VALUES (:id, :name, :approval, :credit, :type)",
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE,
            TableDefinition::SUBJECT_TABLE_FIELD_ID,
            TableDefinition::SUBJECT_TABLE_FIELD_NAME,
            TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
            TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
            TableDefinition::SUBJECT_TABLE_FIELD_TYPE);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql)) {
            throw new DataAccessException(oci_error($stmt));
        }

        $id = $model->getId();
        $name = $model->getName();
        $approval = $model->getApproval();
        $credit = $model->getCredit();
        $type = $model->getType();

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':approval', $approval, -1) ||
            !oci_bind_by_name($stmt, ':credit', $credit, -1) ||
            !oci_bind_by_name($stmt, ':type', $type, -1))
            throw new DataAccessException(oci_error($stmt));


        if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
            throw new DataAccessException(json_encode(oci_error($stmt)));
        }

        // TODO: fix this
        return $this->findByCrit(new Subject($model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(ISubject $model): ISubject {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :name, %s = :approval, %s = :credit, %s = :type WHERE %s = :id",
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE,
            TableDefinition::SUBJECT_TABLE_FIELD_NAME,
            TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
            TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
            TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
            TableDefinition::SUBJECT_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();
        $name = $model->getName();
        $approval = $model->getApproval();
        $credit = $model->getCredit();
        $type = $model->getType();

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':approval', $approval, -1) ||
            !oci_bind_by_name($stmt, ':credit', $credit, -1) ||
            !oci_bind_by_name($stmt, ':type', $type, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Subject($model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(ISubject $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :id",
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE,
            TableDefinition::SUBJECT_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();

        if (!oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(ISubject $model): ISubject
    {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :name, %s = :approval, %s = :credit, %s = :type WHERE %s = :id",
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE,
            TableDefinition::SUBJECT_TABLE_FIELD_NAME,
            TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
            TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
            TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
            TableDefinition::SUBJECT_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();
        $name = $model->getName();
        $approval = $model->getApproval();
        $credit = $model->getCredit();
        $type = $model->getType();

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':approval', $approval, -1) ||
            !oci_bind_by_name($stmt, ':credit', $credit, -1) ||
            !oci_bind_by_name($stmt, ':type', $type, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Subject($model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(ISubject $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :id",
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE,
            TableDefinition::SUBJECT_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();

        if (!oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array
    {
        //TODO: error handling
        $res = array();

        $sql = sprintf("SELECT %s AS ID, %s AS NAME, %s AS APPROVAL, %s AS CREDIT, %s AS TYPE FROM %s.%s",
            TableDefinition::SUBJECT_TABLE_FIELD_ID,
            TableDefinition::SUBJECT_TABLE_FIELD_NAME,
            TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
            TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
            TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Subject(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'APPROVAL'),
                oci_result($stmt, 'CREDIT'),
                oci_result($stmt, 'TYPE')
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ISubject $model): array {
        $res = array();
        $crits = array();


        $sql = sprintf("SELECT %s AS ID, %s AS NAME, %s AS APPROVAL, %s AS CREDIT, %s AS TYPE FROM %s.%s",
            TableDefinition::SUBJECT_TABLE_FIELD_ID,
            TableDefinition::SUBJECT_TABLE_FIELD_NAME,
            TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
            TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
            TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE
        );

        $id = $model->getId();
        $name = $model->getName();
        $type = $model->getType();

        if (isset($id)) $crits[] = TableDefinition::SUBJECT_TABLE_FIELD_ID . " LIKE :id";
        if (isset($name)) $crits[] = TableDefinition::SUBJECT_TABLE_FIELD_NAME . " LIKE :name";
        if (isset($type)) $crits[] = TableDefinition::SUBJECT_TABLE_FIELD_TYPE . " LIKE :type";

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($id) && !oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));
        if (isset($name) && !oci_bind_by_name($stmt, ':name', $name, -1))
            throw new DataAccessException('bind name ' . json_encode(oci_error($stmt)));
        if (isset($type) && !oci_bind_by_name($stmt, ':type', $type, -1))
            throw new DataAccessException('bind type ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Subject(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'APPROVAL'),
                oci_result($stmt, 'CREDIT'),
                oci_result($stmt, 'TYPE')
            );
        }

        return $res;
    }
}