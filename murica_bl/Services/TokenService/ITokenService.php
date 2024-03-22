<?php

namespace murica_bl\Services\TokenService;

use murica_bl_impl\Dto\Token;

interface ITokenService {
    public function generateToken(string $username): array;
    public function verifyToken(string $token): Token|false;
}
