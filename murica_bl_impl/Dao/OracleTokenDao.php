<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ITokenDao;
use murica_bl\Dto\IToken;
use murica_bl\Dto\IUser;
use murica_bl\Services\ConfigService\IDataSourceConfigService;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Dto\Token;
use Override;

class OracleTokenDao implements ITokenDao
{
    //region Properties
    private OracleDataSource $dataSource;
    private IDataSourceConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(OracleDataSource $dataSource, IDataSourceConfigService $configService)
    {
        $this->dataSource = $dataSource;
        $this->configService = $configService;
    }
    //endregion

    #[Override]
    public function findByToken(string $token): Token
    {
        //TODO
        /*$res = array();

        $sql = sprintf("SELECT %s AS ID, %s AS NAME, %s AS EMAIL, %s AS PASSWORD, TO_CHAR(%s,'YYYY-MM-DD') AS BIRTH_DATE 
                              FROM %s.%s WHERE ID LIKE :id",
            TableDefinition::USER_TABLE_FIELD_ID,
            TableDefinition::USER_TABLE_FIELD_NAME,
            TableDefinition::USER_TABLE_FIELD_EMAIL,
            TableDefinition::USER_TABLE_FIELD_PASSWORD,
            TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
            $this->configService->getTableOwner(),
            TableDefinition::USER_TABLE,
        );

        $stmt = oci_parse($this->dataSource->getConnection(), $sql);
        $id = $model->getId();
        oci_bind_by_name($stmt, ':id', $id, -1);
        oci_execute($stmt, OCI_DEFAULT);

        if (!$stmt) {
            throw new DataAccessException(oci_error($stmt));
        }

        while (oci_fetch($stmt)) {
            $res[] = new User(
                oci_result($stmt, 'ID'),
                oci_result($stmt, 'NAME'),
                oci_result($stmt, 'EMAIL'),
                oci_result($stmt, 'PASSWORD'),
                oci_result($stmt, 'BIRTH_DATE')
            );
        }*/
    }

    #[Override]
    public function insert(IToken $model): Token
    {
        // TODO: Implement insert() method.
    }

    #[Override]
    public function remove(IToken $model): void
    {
        // TODO: Implement remove() method.
    }
}