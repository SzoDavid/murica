<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IStudent {
    public function getUser(): ?IUser;
    public function getProgramme(): ?IProgramme;
    public function getStartTerm(): ?string;
    public function setUser(IUser $user): IStudent;
    public function setProgramme(IProgramme $programme): IStudent;
    public function setStartTerm(string $startTerm): IStudent;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}