<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IMessageDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IMessage;
use murica_bl_impl\Dao\Utils\OracleCheckers;
use murica_bl_impl\DataSource\OracleDataSource;
use murica_bl_impl\Dto\Message;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Services\ConfigService\OracleDataSourceConfigService;
use Override;

class OracleMessageDao implements IMessageDao {
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

    //region IMessageDao members
    /**
     * @inheritDoc
     */
    #[Override]
    public function create(IMessage $model): IMessage {
        $model->validate();

        if (!OracleCheckers::checkIfUserExists($model->getUser(), $this->configService, $this->dataSource))
            throw new ValidationException('User with id ' . $model->getUser()->getId() . ' does not exist in datasource');

        $sql = sprintf("INSERT INTO %s.%s (%s, %s, %s, %s) VALUES (:userId, :subject, :content, TO_DATE(:date, 'YYYY-MM-DD HH24:MI'))",
                       $this->configService->getTableOwner(),
                       TableDefinition::MESSAGE_TABLE,
                       TableDefinition::MESSAGE_TABLE_FIELD_USER_ID,
                       TableDefinition::MESSAGE_TABLE_FIELD_SUBJECT,
                       TableDefinition::MESSAGE_TABLE_FIELD_CONTENT,
                       TableDefinition::MESSAGE_TABLE_FIELD_DATE);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        $id = $model->getUser()->getId();
        $subject = $model->getSubject();
        $content = $model->getContent();
        $date = $model->getDateTime();

        if (!oci_bind_by_name($stmt, ':userId', $id, -1) ||
            !oci_bind_by_name($stmt, ':subject', $subject, -1) ||
            !oci_bind_by_name($stmt, ':content', $content, -1) ||
            !oci_bind_by_name($stmt, ':date', $date, -1))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        return $this->findByCrit(new Message($date))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    function findAll(): array {
        $res = array();

        $sql = sprintf("SELECT USR.%s AS ID, USR.%s AS NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE, MSG.%s AS SUBJECT, MSG.%s AS CONTENT, TO_CHAR(MSG.%s, 'YYYY-MM-DD HH24:MI') AS MSG_DATE FROM %s.%s USR, %s.%s MSG WHERE USR.%s = MSG.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::MESSAGE_TABLE_FIELD_SUBJECT,
                       TableDefinition::MESSAGE_TABLE_FIELD_CONTENT,
                       TableDefinition::MESSAGE_TABLE_FIELD_DATE,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::MESSAGE_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::MESSAGE_TABLE_FIELD_USER_ID);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Message(
                oci_result($stmt, 'MSG_DATE'),
                oci_result($stmt, 'SUBJECT'),
                oci_result($stmt, 'CONTENT'),
                new User(
                    oci_result($stmt, 'ID'),
                    oci_result($stmt, 'NAME'),
                    oci_result($stmt, 'EMAIL'),
                    oci_result($stmt, 'PASSWORD'),
                    oci_result($stmt, 'BIRTH_DATE')
                ));
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IMessage $model): array {
        $res = array();
        $crits = array();

        $sql = sprintf("SELECT USR.%s AS ID, USR.%s AS NAME, USR.%s AS EMAIL, USR.%s AS PASSWORD, TO_CHAR(USR.%s,'YYYY-MM-DD') AS BIRTH_DATE, MSG.%s AS SUBJECT, TO_CHAR(MSG.%s) AS CONTENT, TO_CHAR(MSG.%s, 'YYYY-MM-DD HH24:MI') AS MSG_DATE FROM %s.%s USR, %s.%s MSG WHERE USR.%s = MSG.%s",
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::USER_TABLE_FIELD_NAME,
                       TableDefinition::USER_TABLE_FIELD_EMAIL,
                       TableDefinition::USER_TABLE_FIELD_PASSWORD,
                       TableDefinition::USER_TABLE_FIELD_BIRTH_DATE,
                       TableDefinition::MESSAGE_TABLE_FIELD_SUBJECT,
                       TableDefinition::MESSAGE_TABLE_FIELD_CONTENT,
                       TableDefinition::MESSAGE_TABLE_FIELD_DATE,
                       $this->configService->getTableOwner(),
                       TableDefinition::USER_TABLE,
                       $this->configService->getTableOwner(),
                       TableDefinition::MESSAGE_TABLE,
                       TableDefinition::USER_TABLE_FIELD_ID,
                       TableDefinition::MESSAGE_TABLE_FIELD_USER_ID);

        $user = $model->getUser();
        $date = $model->getDateTime();

        if (isset($user) && $user->getId() !== null) {
            $crits[] = TableDefinition::MESSAGE_TABLE_FIELD_USER_ID . " LIKE :userId";
            $userId = $user->getId();
        }
        if (isset($date)) $crits[] = TableDefinition::MESSAGE_TABLE_FIELD_DATE . " LIKE :date";

        if (!empty($crits))
            $sql .= " AND " . implode(" AND ", $crits);

        if (!$stmt = oci_parse($this->dataSource->getConnection(), $sql))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        if (isset($userId) && !oci_bind_by_name($stmt, ':userId', $userId, -1))
            throw new DataAccessException('bind userId ' . json_encode(oci_error($stmt)));
        if (isset($date) && !oci_bind_by_name($stmt, ':date', $date, -1))
            throw new DataAccessException('content date ' . json_encode(oci_error($stmt)));

        if (!oci_execute($stmt, OCI_DEFAULT))
            throw new DataAccessException(json_encode(oci_error($stmt)));

        while (oci_fetch($stmt)) {
            $res[] = new Message(
                oci_result($stmt, 'MSG_DATE'),
                oci_result($stmt, 'SUBJECT'),
                oci_result($stmt, 'CONTENT'),
                new User(
                    oci_result($stmt, 'ID'),
                    oci_result($stmt, 'NAME'),
                    oci_result($stmt, 'EMAIL'),
                    oci_result($stmt, 'PASSWORD'),
                    oci_result($stmt, 'BIRTH_DATE')
                ));
        }

        return $res;
    }
    //endregion
}