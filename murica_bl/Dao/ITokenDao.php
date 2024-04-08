<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\IToken;


interface ITokenDao {
    /**
     * @throws DataAccessException
     */
    public function findByToken(string $token): IToken|false;
    /**
     * @throws DataAccessException
     */
    public function register(string $token, string $userId, int $expirationDate): IToken;
    /**
     * @throws DataAccessException
     */
    public function remove(string $token): void;
}