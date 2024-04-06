<?php

namespace murica_bl\DataSource;

use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\IMessageDao;
use murica_bl\Dao\ITokenDao;
use murica_bl\Dao\IUserDao;

interface IDataSource {
    public function createUserDao(): IUserDao;
    public function createTokenDao(): ITokenDao;
    public function createAdminDao(): IAdminDao;
    public function createMessageDao(): IMessageDao;
}