<?php

namespace murica_bl_impl\Services\TokenService;

use murica_bl\Dao\ITokenDao;
use murica_bl\Exceptions\NotImplementedException;
use murica_bl\Services\TokenService\ITokenService;
use murica_bl_impl\Dto\Token;
use Override;

class DataSourceTokenService implements ITokenService {
    //region Properties
    private ITokenDao $tokenDao;
    //endregion

    //region Ctor
    /**
     * @param ITokenDao $tokenDao
     */
    public function __construct(ITokenDao $tokenDao) {
        $this->tokenDao = $tokenDao;
    }
    //endregion

    //region ITokenService members
    /**
     * @throws NotImplementedException
     */
    #[Override]
    public function generateToken(string $username): array {
        // TODO: Implement generateToken() method.
        throw new NotImplementedException('generateToken not implemented');
    }

    #[Override]
    public function verifyToken(string $token): false|Token {
        $tokenDto = $this->tokenDao->findByToken($token);

        if ($tokenDto && strtotime($tokenDto->getExpiresAt()) > time()) {
            return $tokenDto;
        }

        return false;
    }
    //endregion

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