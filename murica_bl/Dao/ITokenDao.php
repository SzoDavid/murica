<?php

namespace murica_bl\Dao;

use murica_bl\Dto\IToken;
use murica_bl_impl\Dto\Token;


interface ITokenDao
{
    public function findByToken(string $token): Token;
    public function insert(IToken $model): Token;
    public function remove(IToken $model): void;
}