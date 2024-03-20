<?php

namespace murica_bl\Services\TokenService;

use murica_bl\Services\TokenService\_Interfaces\ITokenService;

class ArrayTokenService implements ITokenService
{
    public array $tokens;

    public function __construct()
    {
        $this->tokens = [
            '0b8d1835-2fda-4cd0-af11-1f737cec65e0' => ['username' => 'YTWK3B', 'expires_at' => 1711042089],
        ];
    }

    #[\Override]
    public function generateToken(string $username): array
    {
        // TODO: check if username already has token
        do {
            $token = $this->guidv4();
        } while (isset($this->tokens[$token]));

        $expirationDate = time() + (24 * 60 * 60);
        $this->tokens[$token] = ['username' => $username, 'expires_at' => $expirationDate];

        return ['token' => $token, 'expires_at' => $expirationDate];
    }

    #[\Override]
    public function verifyToken(string $token): bool
    {
        return isset($this->tokens[$token]) && $this->tokens[$token]['expires_at'] > time();
    }

    private function guidv4($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
