<?php

namespace murica_bl\DAO\_Interfaces;

use murica_bl\DTO\_Interfaces\IUser;

interface IUserDao
{
    public function findAll(): array;
    public function findByCrit(IUser $model): IUser;
    public function insert(IUser $model): IUser;
    public function remove(IUser $model): void;
}