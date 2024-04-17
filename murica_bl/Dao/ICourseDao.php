<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ICourse;

interface ICourseDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(ICourse $model): ICourse;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(ICourse $model): ICourse;
    /**
     * @throws DataAccessException
     */
    public function delete(ICourse $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(ICourse $model): array;
}