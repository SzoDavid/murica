<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IRoom;

interface IRoomDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IRoom $model): IRoom;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(IRoom $model): IRoom;
    /**
     * @throws DataAccessException
     */
    public function delete(IRoom $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IRoom $model): array;
    /**
     * @throws DataAccessException
     */
    public function getRoomIdWithMostMathSubjects(): IRoom;
    /**
     * @throws DataAccessException
     */
    public function getRoomIdWithMostInfoSubjects(): IRoom;

}