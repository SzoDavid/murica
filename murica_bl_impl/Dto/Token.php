<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\IToken;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class Token Extends Entity implements IToken
{
    //region Properties
    private string $token;
    private IUser $user;
    private string $expiresAt;
    //endregion

    //region Ctor
    /**
     * @param string $token
     * @param IUser $user
     * @param string $expiresAt
     */
    public function __construct(string $token, IUser $user, string $expiresAt)
    {
        $this->token = $token;
        $this->user = $user;
        $this->expiresAt = $expiresAt;
    }
    //endregion

    //region Getters
    #[Override]
    public function getToken(): string
    {
        return $this->token;
    }

    #[Override]
    public function getUser(): IUser
    {
        return $this->user;
    }

    #[Override]
    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }
    //endregion

    //region JsonSerializable members
    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'user' => $this->user,
            'expires_at' => $this->expiresAt
        ];
    }
    //endregion
}