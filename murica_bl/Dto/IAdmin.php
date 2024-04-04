<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IAdmin {
    public function getUser(): IUser;
    public function setUser(IUser $user): IAdmin;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}