<?php

namespace murica_bl\Dto;

interface IToken {
    public function getToken(): string;
    public function getUser(): IUser;
    public function getExpiresAt(): string;
}