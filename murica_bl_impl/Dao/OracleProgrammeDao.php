<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dto\IProgramme;
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $name = $model->getName();
        $type = $model->getType();
        $noTerms = $model->getNoTerms();

        if (!oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':type', $type, -1) ||
            !oci_bind_by_name($stmt, ':noTerms', $noTerms, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $name = $model->getName();
        $type = $model->getType();
        $noTerms = $model->getNoTerms();

        if (!oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':type', $type, -1) ||
            !oci_bind_by_name($stmt, ':noTerms', $noTerms, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $name = $model->getName();
        $type = $model->getType();

        if (!oci_bind_by_name($stmt, ':name', $name, -1) ||
            !oci_bind_by_name($stmt, ':type', $type, -1))
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

        $sql = sprintf("SELECT %s AS NAME, %s AS TYPE, %s AS NO_TERMS FROM %s.%s",
                       TableDefinition::PROGRAMME_TABLE_FIELD_NAME,
                       TableDefinition::PROGRAMME_TABLE_FIELD_TYPE,
                       TableDefinition::PROGRAMME_TABLE_FIELD_NO_TERMS,
                       $this->configService->getTableOwner(),
                       TableDefinition::PROGRAMME_TABLE);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Programme(
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'TYPE'),
                oci_result($stmt, 'NO_TERMS')
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IProgramme $model): array {
        $res = array();
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

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        if (isset($name) && !oci_bind_by_name($stmt, ':name', $name, -1))
            throw new DataAccessException('bind name ' . json_encode(oci_error($stmt)));
        if (isset($type) && !oci_bind_by_name($stmt, ':type', $type, -1))
            throw new DataAccessException('bind type ' . json_encode(oci_error($stmt)));
        if (isset($noTerms) && !oci_bind_by_name($stmt, ':noTerms', $noTerms, -1))
            throw new DataAccessException('bind noTerms ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Programme(
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'TYPE'),
                oci_result($stmt, 'NO_TERMS')
            );
        }

        return $res;
    }
    //endregion
}