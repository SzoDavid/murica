<?php

namespace murica_bl\Services\TokenService\_Interfaces;

interface ITokenService
{
    public function generateToken(string $username): array;
    public function verifyToken(string $token): bool;
}
