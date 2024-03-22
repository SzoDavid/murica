<?php

namespace murica_bl\Services\TokenService;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dto\IToken;

interface ITokenService {
    /**
     * @throws DataAccessException
     */
    public function generateToken(string $userId): IToken;
    /**
     * @throws DataAccessException
     */
    public function verifyToken(string $token): IToken|false;
    /**
     * @throws DataAccessException
     */
    public function removeToken(string $token): void;
}
