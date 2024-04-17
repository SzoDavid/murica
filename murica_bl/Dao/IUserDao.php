<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IUser;

interface IUserDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IUser $model): IUser;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(IUser $model): IUser;
    /**
     * @throws DataAccessException
     */
    public function delete(IUser $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IUser $model): array;
}