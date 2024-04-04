<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IUser;
use murica_bl_impl\Dto\User;

interface IUserDao {
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IUser $model): array;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IUser $model): User;
    public function remove(IUser $model): void;
}