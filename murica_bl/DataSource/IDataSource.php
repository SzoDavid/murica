<?php

namespace murica_bl\DataSource;

use murica_bl\Dao\IUserDao;

interface IDataSource {
    public function createUserDao(): IUserDao;
}