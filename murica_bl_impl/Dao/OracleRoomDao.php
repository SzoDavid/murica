<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IRoomDao;
use murica_bl\Orm\Exception\OciException;
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

        $id = $model->getId();
        $capacity = $model->getCapacity();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->bind(':capacity', $capacity)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create room', $e);
        }

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

        $id = $model->getId();
        $capacity = $model->getCapacity();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->bind(':capacity', $capacity)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to update room', $e);
        }

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

        $id = $model->getId();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->execute(OCI_COMMIT_ON_SUCCESS)
                ->free();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to delete room', $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findAll(): array {
        $sql = sprintf("SELECT %s AS ID, %s AS CAPACITY FROM %s.%s",
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE
        );

        try {
            $rooms = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query rooms', $e);
        }

        return $this->fetchRooms($rooms);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IRoom $model): array {
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

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($id)) $stmt->bind(':id', $id);
            if (isset($capacity)) $stmt->bind(':capacity', $capacity);

            $rooms = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query rooms', $e);
        }
        return $this->fetchRooms($rooms);
    }

    #[Override]
    public function getRoomIdWithMostMathSubjects(): IRoom {
        $sql = sprintf("SELECT %s AS ID, %s AS CAPACITY
                    FROM (
                             SELECT ROOM.%s, ROOM.%s, COUNT(*) AS math_subject_count
                             FROM %s.%s CRS, %s.%s SBJ, %s.%s ROOM 
                             WHERE CRS.%s = SBJ.%s AND CRS.%s = ROOM.%s AND SBJ.%s = 'Matematika'
                             GROUP BY ROOM.%s, ROOM.%s
                             ORDER BY math_subject_count DESC
                         )
                    WHERE ROWNUM = 1",
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY);

        try {
            $room = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->firstResult();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query room', $e);
        }

        return $this->fetchRoom($room);
    }

    #[Override]
    public function getRoomIdWithMostInfoSubjects(): IRoom {
        $sql = sprintf("SELECT %s AS ID, %s AS CAPACITY
                    FROM (
                             SELECT ROOM.%s, ROOM.%s, COUNT(*) AS math_subject_count
                             FROM %s.%s CRS, %s.%s SBJ, %s.%s ROOM 
                             WHERE CRS.%s = SBJ.%s AND CRS.%s = ROOM.%s AND SBJ.%s = 'Informatika'
                             GROUP BY ROOM.%s, ROOM.%s
                             ORDER BY math_subject_count DESC
                         )
                    WHERE ROWNUM = 1",
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY,
                       $this->configService->getTableOwner(),
                       TableDefinition::COURSE_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::SUBJECT_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::ROOM_TABLE,
                       TableDefinition::COURSE_TABLE_FIELD_SUBJECT_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_ID,
                       TableDefinition::COURSE_TABLE_FIELD_ROOM_ID,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::SUBJECT_TABLE_FIELD_TYPE,
                       TableDefinition::ROOM_TABLE_FIELD_ID,
                       TableDefinition::ROOM_TABLE_FIELD_CAPACITY);

        try {
            $room = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->firstResult();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query room', $e);
        }

        return $this->fetchRoom($room);
    }
    //endregion

    private function fetchRooms(array $rooms): array {
        $res = array();

        foreach ($rooms as $room) {
            $res[] = $this->fetchRoom($room);
        }

        return $res;
    }

    private function fetchRoom(array $room): IRoom {
        return new Room(
            $room['ID'],
            $room['CAPACITY']);
    }
}
