<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface ISubject {
    public function getId(): ?string;
    public function getName(): ?string;
    public function isApproved(): ?bool;
    public function getCredit(): ?int;
    public function getType(): ?string;
    public function setId(string $id): ISubject;
    public function setName(string $name): ISubject;
    public function setApproved(bool $approval): ISubject;
    public function setCredit(int $credit): ISubject;
    public function setType(string $type): ISubject;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}