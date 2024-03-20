<?php

namespace murica_bl\DTO\_Interfaces;

interface IUser
{
    public function getId(): string;
    public function getName(): string;
    public function getEmail(): string;
    public function getPassword(): string;
    public function getBirthDate(): string;

    public function setId(string $id): IUser;
    public function setName(string $name): IUser;
    public function setEmail(string $email): IUser;
    public function setPassword(string $password): IUser;
    public function setBirthDate(string $birth_date): IUser;
}