<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IAdmin;

interface IAdminDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IAdmin $model): IAdmin;
    /**
     * @throws DataAccessException
     */
    public function delete(IAdmin $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IAdmin $model): array;
}