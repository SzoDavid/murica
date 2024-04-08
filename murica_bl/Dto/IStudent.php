<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IStudent {
    public function getUser(): ?IUser;
    public function getProgrammeName(): ?string;
    public function getProgrammeType(): ?string;
    public function getStartTerm(): ?string;
    public function setUser(IUser $user): IStudent;
    public function setProgrammeName(string $programname): IStudent;
    public function setProgrammeType(string $type): IStudent;
    public function setStartTerm(string $startterm): IStudent;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}