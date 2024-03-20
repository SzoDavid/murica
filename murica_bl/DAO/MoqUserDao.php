<?php

namespace murica_bl\DAO;

use murica_bl\DAO\_Interfaces\IUserDao;
use murica_bl\DTO\_Interfaces\IUser;
use murica_bl\DTO\User;


class MoqUserDao implements IUserDao
{
    #[\Override]
    public function remove(IUser $model): void
    {}

    #[\Override]
    public function insert(IUser $model): IUser
    {
        return new User("YTWK3B", "Szobonya Dávid", "szobonya.david@gmail.com", "asd", "2003-05-22");
    }

    #[\Override]
    public function findAll(): array
    {
        return [
            new User("YTWK3B", "Szobonya Dávid", "szobonya.david@gmail.com", "asd", "2003-05-22"),
            new User("YTWK3D", "Szobonya Eávid", "szobonya.david@gmail.com", "asd", "2003-05-22"),
            new User("YTWK3E", "Szobonya Fávid", "szobonya.david@gmail.com", "asd", "2003-05-22"),
        ];
    }

    #[\Override]
    public function findByCrit(IUser $model): IUser
    {
        return new User("YTWK3B", "Szobonya Dávid", "szobonya.david@gmail.com", "asd", "2003-05-22");
    }
}