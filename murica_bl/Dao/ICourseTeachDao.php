<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ICourseTeach;

interface ICourseTeachDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(ICourseTeach $model): ICourseTeach;
    /**
     * @throws DataAccessException
     */
    public function delete(ICourseTeach $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(ICourseTeach $model): array;
}