<?php

namespace murica_bl_impl\Dao\Utils;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\IUser;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;

class OracleCheckers {
    /**
     * @throws DataAccessException
     */
    public static function checkIfUserExists(IUser $user, OracleDataSourceConfigService $configService, OracleDataSource $dataSource): bool {
        $sql = sprintf("SELECT * FROM %s.%s WHERE %s = :id",
                       $configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID);

        if (!$stmt = oci_parse($dataSource->getConnection(), $sql))
            throw new DataAccessException('parse ' . json_encode(oci_error($stmt)));

        $id = $user->getId();

        if (!oci_bind_by_name($stmt, ':id', $id, -1))
            throw new DataAccessException('bind id ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException('exec ' . json_encode(oci_error($stmt)));

        return oci_fetch($stmt);
    }
}