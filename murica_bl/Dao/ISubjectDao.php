<?php

namespace murica_bl\Dao;

use Cassandra\Exception\ValidationException;
use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\ISubject;

interface ISubjectDao {

    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function create(ISubject $model): ISubject;
    /**
     * @throws DataAccessException
     * @throws ValidationException
     */
    public function update(ISubject $model): ISubject;
    /**
     * @throws DataAccessException
     */
    public function delete(ISubject $model): void;
    /**
     * @throws DataAccessException
     */
    public function findAll(): array;
    /**
     * @throws DataAccessException
     */
    public function findByCrit(ISubject $model): array;

}