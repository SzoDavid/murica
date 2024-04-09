<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IRoomDao;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Room;
use murica_bl\Dto\IRoom;
use Override;

class OracleRoomDao implements IRoomDao {
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

    //region IRoomDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(IRoom $model): IRoom {
        $model->validate();

        $sql = sprintf("INSERT INTO %s.%s (%s, %s) VALUES (:id, :capacity)",
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();
        $capacity = $model->getCapacity();

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':capacity', $capacity, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Room($model->getId()))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function update(IRoom $model): IRoom {
        $model->validate();
       
        $sql = sprintf("UPDATE %s.%s SET %s = :capacity WHERE %s = :id",
            $this->configService->getTableOwner(),
            TableDefinition::ROOM_TABLE,
            TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
            TableDefinition::ROOM_TABLE_FIELD_ID
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getId();
        $capacity = $model->getCapacity();

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':capacity', $capacity, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Room($model->getId()))[0];
    }


    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(IRoom $model): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s = :id",
            $this->configService->getTableOwner(),
            TableDefinition::ROOM_TABLE,
            TableDefinition::ROOM_TABLE_FIELD_ID
        );

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
    public function findAll(): array {
        $res = array();

        $sql = sprintf("SELECT %s AS ID, %s AS CAPACITY FROM %s.%s",
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_bind_by_name($stmt, ':id', $id, -1) ||
            !oci_bind_by_name($stmt, ':capacity', $capacity, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Room(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'CAPACITY')
            );
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IRoom $model): array {
        $res = array();
        $crits = array();

        $sql = sprintf("SELECT ROOM.%s AS ID, ROOM.%s AS CAPACITY FROM %s.%s ROOM",
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE);

        $id = $model->getId();
        $capacity = $model->getCapacity();

        if (isset($id)) $crits[] = TableDefinition::ROOM_TABLE_FIELD_ID . " LIKE :id";
        if (isset($capacity)) $crits[] = TableDefinition::ROOM_TABLE_FIELD_CAPACITY . " = :capacity";

        if (!empty($crits))
            $sql .= " WHERE " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (isset($id) && !oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));
        if (isset($capacity) && !oci_bind_by_name($stmt, ':capacity', $capacity, -1))
            throw new DataAccessException('bind capacity ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Room(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'CAPACITY')
            );
        }

        return $res;
    }
    //endregion
}
