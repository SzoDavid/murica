<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IStudent;

interface IStudentDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IStudent $model): IStudent;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(IStudent $model): IStudent;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function delete(IStudent $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IStudent $model): array;
}