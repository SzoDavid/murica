<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dto\IProgramme;
use murica_bl\Orm\Exception\OciException;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleProgrammeDao implements IProgrammeDao {
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

    //region IProgrammeDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(IProgramme $model): IProgramme {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s) VALUES (:name, :type, :noTerms)",
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS);

        $name = $model->getName();
        $type = $model->getType();
        $noTerms = $model->getNoTerms();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':name', $name)
                ->bind(':type', $type)
                ->bind(':noTerms', $noTerms)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create programme', $e);
        }

        return $this->findByCrit(new Programme($name, $type))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(IProgramme $model): IProgramme {
        $model->validate();

        $sql = sprintf("UPDATE %s.%s SET %s = :noTerms WHERE %s = :name AND %s = :type",
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE);

        $name = $model->getName();
        $type = $model->getType();
        $noTerms = $model->getNoTerms();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':name', $name)
                ->bind(':type', $type)
                ->bind(':noTerms', $noTerms)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to update programme', $e);
        }

        return $this->findByCrit(new Programme($name, $type))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(IProgramme $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :name AND %s = :type",
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE);

        $name = $model->getName();
        $type = $model->getType();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':name', $name)
                ->bind(':type', $type)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete programme', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $sql = sprintf("SELECT %s AS NAME, %s AS TYPE, %s AS NO_TERMS FROM %s.%s",
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE);

        try {
            $programmes = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query programmes', $e);
        }

        return $this->fetchProgrammes($programmes);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IProgramme $model): array {
        $crits = array();

        $sql = sprintf("SELECT %s AS NAME, %s AS TYPE, %s AS NO_TERMS FROM %s.%s",
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE);

        $name = $model->getName();
        $type = $model->getType();
        $noTerms = $model->getNoTerms();

        if (isset($name)) $crits[] = TableDefinition::PROGRAMME_TABLE_FIELD_NAME . " LIKE :name";
        if (isset($type)) $crits[] = TableDefinition::PROGRAMME_TABLE_FIELD_TYPE . " LIKE :type";
        if (isset($noTerms)) $crits[] = TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS . " = :noTerms";

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($name)) $stmt->bind(':name', $name);
            if (isset($type)) $stmt->bind(':type', $type);
            if (isset($noTerms)) $stmt->bind(':noTerms', $noTerms);

            $programmes = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query programmes', $e);
        }

        return $this->fetchProgrammes($programmes);
    }
    //endregion

    private function fetchProgrammes(array $programmes): array {
        $res = array();

        foreach ($programmes as $programme) {
            $res[] = new Programme(
                $programme['NAME'],
                $programme['TYPE'],
                $programme['NO_TERMS']);
        }

        return $res;
    }
}