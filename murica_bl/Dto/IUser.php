<?php

namespace murica_bl\Dto;

interface IUser {
    public function getId(): ?string;
    public function getName(): ?string;
    public function getEmail(): ?string;
    public function getPassword(): ?string;
    public function getBirthDate(): ?string;
}