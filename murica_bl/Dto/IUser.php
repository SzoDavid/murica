<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IUser {
    public function getId(): ?string;
    public function getName(): ?string;
    public function getEmail(): ?string;
    public function getPassword(): ?string;
    public function getBirthDate(): ?string;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}