<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IProgramme;

interface IProgrammeDao {
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(IProgramme $model): IProgramme;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(IProgramme $model): IProgramme;
    /**
     * @throws DataAccessException
     */
    public function delete(IProgramme $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(IProgramme $model): array;
}