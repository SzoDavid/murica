<?php

namespace murica_bl_impl\DataSource;

use Exception;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\ICourseDao;
use murica_bl\Dao\ICourseTeachDao;
use murica_bl\Dao\IExamDao;
use murica_bl\Dao\IMessageDao;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dao\IRoomDao;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dao\ITakenCourseDao;
use murica_bl\Dao\ITakenExamDao;
use murica_bl\Dao\ITokenDao;
use murica_bl\Dao\IUserDao;
use murica_bl\DataSource\Exceptions\DataSourceException;
use murica_bl\DataSource\IDataSource;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\Dao\OracleAdminDao;
use murica_bl_impl\Dao\OracleCourseDao;
use murica_bl_impl\Dao\OracleCourseTeachDao;
use murica_bl_impl\Dao\OracleExamDao;
use murica_bl_impl\Dao\OracleMessageDao;
use murica_bl_impl\Dao\OracleProgrammeDao;
use murica_bl_impl\Dao\OracleRoomDao;
use murica_bl_impl\Dao\OracleStudentDao;
use murica_bl_impl\Dao\OracleSubjectDao;
use murica_bl_impl\Dao\OracleTakenCourseDao;
use murica_bl_impl\Dao\OracleTakenExamDao;
use murica_bl_impl\Dao\OracleTokenDao;
use murica_bl_impl\Dao\OracleUserDao;
use murica_bl_impl\Orm\OracleOrm;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleDataSource implements IDataSource {
    //region Properties
    private IDataSourceConfigService $configService;
    private $connection;
    //endregion

    //region Ctor
    /**
     * @throws DataSourceException
     */
    public function __construct(IDataSourceConfigService $configService) {
        $this->configService = $configService;

        try {
            if (!$configService instanceof OracleDataSourceConfigService) {
                throw new DataSourceException("Data source configs are invalid");
            }

            $this->connection = new OracleOrm(
                $configService->getUser(),
                $configService->getPassword(),
                $configService->getConnectionString()
            );

        } catch (Exception $ex) {
            throw new DataSourceException('Could not establish database connection', $ex);
        }
    }

    public function __destruct() {
        $this->connection->close();
    }

    //endregion

    //region Create daos
    #[Override]
    public function createUserDao(): IUserDao {
        return new OracleUserDao($this, $this->configService);
    }

    #[Override]
    public function createTokenDao(): ITokenDao {
        return new OracleTokenDao($this, $this->configService);
    }

    #[Override]
    public function createAdminDao(): IAdminDao {
        return new OracleAdminDao($this, $this->configService);
    }

    #[Override]
    public function createMessageDao(): IMessageDao {
        return new OracleMessageDao($this, $this->configService);
    }

    #[Override]
    public function createProgrammeDao(): IProgrammeDao {
        return new OracleProgrammeDao($this, $this->configService);
    }

    #[Override]
    public function createRoomDao(): IRoomDao {
        return new OracleRoomDao($this, $this->configService);
    }

    #[Override]
    public function createStudentDao(): IStudentDao {
        return new OracleStudentDao($this, $this->configService);
    }

    #[Override]
    public function createSubjectDao(): ISubjectDao {
        return new OracleSubjectDao($this, $this->configService);
    }

    #[Override]
    public function createCourseDao(): ICourseDao {
        return new OracleCourseDao($this, $this->configService);
    }

    #[Override]
    public function createCourseTeachDao(): ICourseTeachDao {
        return new OracleCourseTeachDao($this, $this->configService);
    }

    #[Override]
    public function createTakenCourseDao(): ITakenCourseDao {
        return new OracleTakenCourseDao($this, $this->configService);
    }

    #[Override]
    public function createExamDao(): IExamDao {
        return new OracleExamDao($this, $this->configService);
    }

    #[Override]
    public function createTakenExamDao(): ITakenExamDao {
        return new OracleTakenExamDao($this, $this->configService);
    }
    //endregion

    public function getConnection(): OracleOrm {
        return $this->connection;
    }
}