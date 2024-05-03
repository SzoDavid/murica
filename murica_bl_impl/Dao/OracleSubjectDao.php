<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dto\ISubject;
use murica_bl\Orm\Exception\OciException;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Subject;
use Override;

class OracleSubjectDao implements ISubjectDao {

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

        $id = $model->getId();
        $name = $model->getName();
        $approval = $model->isApprovalNeeded() ? 1 : 0;
        $credit = $model->getCredit();
        $type = $model->getType();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->bind(':name', $name)
                ->bind(':approval', $approval)
                ->bind(':credit', $credit)
                ->bind(':type', $type)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create subject', $e);
        }

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

        $id = $model->getId();
        $name = $model->getName();
        $approval = $model->isApprovalNeeded() ? 1 : 0;
        $credit = $model->getCredit();
        $type = $model->getType();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->bind(':name', $name)
                ->bind(':approval', $approval)
                ->bind(':credit', $credit)
                ->bind(':type', $type)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to update subject', $e);
        }

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

        $id = $model->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete subject', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $sql = sprintf("SELECT %s AS ID, %s AS NAME, %s AS APPROVAL, %s AS CREDIT, %s AS TYPE FROM %s.%s",
            TableDefinition::SUBJECT_TABLE_FIELD_ID,
            TableDefinition::SUBJECT_TABLE_FIELD_NAME,
            TableDefinition::SUBJECT_TABLE_FIELD_APPROVAL,
            TableDefinition::SUBJECT_TABLE_FIELD_CREDIT,
            TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
            $this->configService->getTableOwner(),
            TableDefinition::SUBJECT_TABLE
        );

        try {
            $subjects = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query subjects', $e);
        }

        return $this->fetchSubjects($subjects);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(ISubject $model): array {
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

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($name)) $stmt->bind(':name', $name);
            if (isset($type)) $stmt->bind(':type', $type);
            if (isset($id)) $stmt->bind(':id', $id);

            $subjects = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query subjects', $e);
        }

        return $this->fetchSubjects($subjects);
    }

    private function fetchSubjects(array $subjects): array {
        $res = array();

        foreach ($subjects as $subject) {
            $res[] = new Subject(
                $subject['ID'],
                $subject['NAME'],
                $subject['APPROVAL'] == 1,
                $subject['CREDIT'],
                $subject['TYPE'],
            );
        }

        return $res;
    }
}