<?php

namespace murica_bl_impl\Dao;

use murica_bl\Dao\IUserDao;
use murica_bl\Dto\IUser;
use murica_bl_impl\Dto\User;


class MoqUserDao implements IUserDao
{
    #[\Override]
    public function remove(IUser $model): void
    {}

    #[\Override]
    public function insert(IUser $model): User
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
    public function findByCrit(IUser $model): array
    {
        return [new User("YTWK3B", "Szobonya Dávid", "szobonya.david@gmail.com", "asd", "2003-05-22")];
    }
}