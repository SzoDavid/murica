<?php

namespace murica_bl\Dao;

use murica_bl\Dto\IUser;
use murica_bl_impl\Dto\User;

interface IUserDao {
    public function findAll(): array;
    public function findByCrit(IUser $model): array;
    public function insert(IUser $model): User;
    public function remove(IUser $model): void;
}