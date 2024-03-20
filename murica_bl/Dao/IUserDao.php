<?php

namespace murica_bl\Dao;

use murica_bl\Dto\IUser;

interface IUserDao
{
    public function findAll(): array;
    public function findByCrit(IUser $model): IUser;
    public function insert(IUser $model): IUser;
    public function remove(IUser $model): void;
}