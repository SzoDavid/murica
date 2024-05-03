<?php

namespace murica_bl_impl\Dao;

use murica_bl\Constants\TableDefinition;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IMessageDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IMessage;
use murica_bl\Orm\Exception\OciException;
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

        $id = $model->getUser()->getId();
        $subject = $model->getSubject();
        $content = $model->getContent();
        $date = $model->getDateTime();

        try {
            $this->dataSource->getConnection()
                ->query($sql)
                ->bind(':userId', $id)
                ->bind(':subject', $subject)
                ->bind(':content', $content)
                ->bind(':date', $date)
                ->execute(OCI_COMMIT_ON_SUCCESS);
        } catch (OciException $e) {
            throw new DataAccessException('Failed to create message', $e);
        }

        return $this->findByCrit(new Message($date))[0];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    function findAll(): array {
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

        try {
            $messages = $this->dataSource->getConnection()
                ->query($sql)
                ->execute(OCI_DEFAULT)
                ->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query messages', $e);
        }

        return $this->fetchMessages($messages);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function findByCrit(IMessage $model): array {
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

        try {
            $stmt = $this->dataSource->getConnection()->query($sql);

            if (isset($userId)) $stmt->bind(':userId', $userId);
            if (isset($date)) $stmt->bind(':date', $date);

            $messages = $stmt->execute(OCI_DEFAULT)->result();
        } catch (OciException $e) {
            throw new DataAccessException('Failed to query messages', $e);
        }

        return $this->fetchMessages($messages);
    }
    //endregion

    private function fetchMessages(array $messages): array {
        $res = array();

        foreach ($messages as $message) {
            $res[] = new Message(
                $message['MSG_DATE'],
                $message['SUBJECT'],
                $message['CONTENT'],
                new User(
                    $message['ID'],
                    $message['NAME'],
                    $message['EMAIL'],
                    $message['PASSWORD'],
                    $message['BIRTH_DATE']));
        }

        return $res;
    }
}