<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IStudent {
    public function getUser(): ?IUser;
    public function getProgrammeName(): ?string;
    public function getProgrammeType(): ?string;
    public function getStartTerm(): ?string;
    public function setUser(IUser $user): IStudent;
    public function setProgrammeName(string $programName): IStudent;
    public function setProgrammeType(string $programmeType): IStudent;
    public function setStartTerm(string $startTerm): IStudent;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}