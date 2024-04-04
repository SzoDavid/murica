<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IUser {
    public function getId(): ?string;
    public function getName(): ?string;
    public function getEmail(): ?string;
    public function getPassword(): ?string;
    public function getBirthDate(): ?string;
    public function setId(string $id): IUser;
    public function setName(string $name): IUser;
    public function setEmail(string $email): IUser;
    public function setPassword(string $password): IUser;
    public function setBirthDate(string $birthDate): IUser;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}