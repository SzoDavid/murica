<?php

namespace murica_bl\Dao;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\IToken;
use murica_bl_impl\Dto\Token;


interface ITokenDao {
    /**
     * @throws DataAccessException
     */
    public function findByToken(string $token): Token|false;
    /**
     * @throws DataAccessException
     */
    public function register(string $token, string $userId, int $expirationDate): Token;
    public function remove(string $token): void;
}