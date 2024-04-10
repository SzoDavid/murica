<?php

namespace murica_bl\DataSource;

use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\ICourseDao;
use murica_bl\Dao\IMessageDao;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dao\IRoomDao;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dao\ITokenDao;
use murica_bl\Dao\IUserDao;

interface IDataSource {
    public function createUserDao(): IUserDao;
    public function createTokenDao(): ITokenDao;
    public function createAdminDao(): IAdminDao;
    public function createMessageDao(): IMessageDao;
    public function createProgrammeDao(): IProgrammeDao;
    public function createRoomDao(): IRoomDao;
    public function createStudentDao(): IStudentDao;
    public function createSubjectDao(): ISubjectDao;
    public function createCourseDao(): ICourseDao;
}