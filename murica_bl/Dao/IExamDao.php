<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IExam;

interface IExamDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IExam $model): IExam;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(IExam $model): IExam;
    /**
     * @throws DataAccessException
     */
    public function delete(IExam $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IExam $model): array;
}