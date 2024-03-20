<?php

namespace murica_bl\Services\TokenService;

interface ITokenService
{
    public function generateToken(string $username): array;
    public function verifyToken(string $token): bool;
}
