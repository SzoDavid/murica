<?php

namespace murica_bl_impl\Dao\Utils;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\IUser;
use murica_bl\Orm\Exception\OciException;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;

class OracleCheckers {
    /**
     * @throws DataAccessException
     */
    public static function checkIfUserExists(IUser $user, OracleDataSourceConfigService $configService, OracleDataSource $dataSource): bool {
        $sql = "SELECT * FROM {${$configService->getTableOwner()}}.{${TableDefinition::USER_TABLE}} WHERE %s = :id";

        $id = $user->getId();

        try {
            return empty($dataSource->getConnection()
                ->query($sql)
                ->bind(':id', $id)
                ->execute(OCI_DEFAULT)
                ->result());
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query users', $e);
        }
    }
}