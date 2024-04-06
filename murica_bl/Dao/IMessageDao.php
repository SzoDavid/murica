<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IMessage;

interface IMessageDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IMessage $model): IMessage;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IMessage $model): array;
}