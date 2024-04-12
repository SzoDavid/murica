<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ITakenCourse;

interface ITakenCourseDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(ITakenCourse $model): ITakenCourse;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(ITakenCourse $model): ITakenCourse;
    /**
     * @throws DataAccessException
     */
    public function delete(ITakenCourse $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(ITakenCourse $model): array;
}