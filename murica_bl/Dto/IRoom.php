<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IRoom {
    public function getId(): ?string;
    public function getCapacity(): ?int;
    public function setCapacity(int $capacity): IRoom;
    public function setId(string $id): IRoom;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}