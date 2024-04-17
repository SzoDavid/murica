<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ITokenDao;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Dto\Token;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleTokenDao implements ITokenDao {
    //region Properties
    private OracleDataSource $dataSource;
    private OracleDataSourceConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(OracleDataSource $dataSource, OracleDataSourceConfigService $configService) {
        $this->dataSource = $dataSource;
        $this->configService = $configService;
    }
    //endregion

    //region ITokenDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function findByToken(string $token): Token|false {
        $sql = sprintf("SELECT TOKENS.%s AS TOKEN, TO_CHAR(TOKENS.%s, 'YYYY-MM-DD HH24:MI') AS EXPIRES_AT,
                                      USERS.%s AS ID, USERS.%s AS NAME, USERS.%s AS EMAIL, USERS.%s AS PASSWORD, 
                                      TO_CHAR(USERS.%s,'YYYY-MM-DD') AS BIRTH_DATE
                               FROM %s.%s TOKENS, %s.%s USERS
                               WHERE TOKENS.%s=USERS.%s AND TOKENS.%s=:token",
            TableDefinition::TOKEN_TABLE_FIELD_TOKEN,
            TableDefinition::TOKEN_TABLE_FIELD_EXPIRES_AT,
            TableDefinition::USER_TABLE_FIELD_ID,
            TableDefinition::USER_TABLE_FIELD_NAME,
            TableDefinition::USER_TABLE_FIELD_EMAIL,
            TableDefinition::USER_TABLE_FIELD_PASSWORD,
            TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
            $this->configService->getTableOwner(),
            TableDefinition::TOKEN_TABLE,
            $this->configService->getTableOwner(),
            TableDefinition::USER_TABLE,
            TableDefinition::TOKEN_TABLE_FIELD_USER_ID,
            TableDefinition::USER_TABLE_FIELD_ID,
            TableDefinition::TOKEN_TABLE_FIELD_TOKEN
        );

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_bind_by_name($stmt, ':token', $token, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_fetch($stmt)) return false;

        return new Token(
            oci_result($stmt, 'TOKEN'),
            new User(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'EMAIL'),
                oci_result($stmt, 'PASSWORD'),
                oci_result($stmt, 'BIRTH_DATE')),
            oci_result($stmt, 'EXPIRES_AT'));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function register(string $token, string $userId, int $expirationDate): Token {
        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s)
            VALUES (:token, :userId, TO_DATE(:expirationDate, 'YYYY-MM-DD HH24:MI'))",
                       $this->configService->getTableOwner(),
                       TableDefinition::TOKEN_TABLE,
                       TableDefinition::TOKEN_TABLE_FIELD_TOKEN,
                       TableDefinition::TOKEN_TABLE_FIELD_USER_ID,
                       TableDefinition::TOKEN_TABLE_FIELD_EXPIRES_AT);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $date = date('Y-m-d H:i', $expirationDate);
        if (!oci_bind_by_name($stmt, ':token', $token, -1) ||
            !oci_bind_by_name($stmt, ':userId', $userId, -1) ||
            !oci_bind_by_name($stmt, ':expirationDate', $date, -1))
                throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByToken($token);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function remove(string $token): void {
        $sql = sprintf("DELETE FROM %s.%s WHERE %s=:token",
                       $this->configService->getTableOwner(),
                       TableDefinition::TOKEN_TABLE,
                       TableDefinition::TOKEN_TABLE_FIELD_TOKEN);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_bind_by_name($stmt, ':token', $token, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));
    }
    //endregion
}