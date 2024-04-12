<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ITakenExam;

interface ITakenExamDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(ITakenExam $model): ITakenExam;
    /**
     * @throws DataAccessException
     */
    public function delete(ITakenExam $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(ITakenExam $model): array;
}