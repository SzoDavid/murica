<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IProgramme {
    public function getName(): ?string;
    public function getType(): ?string;
    public function getNoTerms(): ?int;

    public function setName(string $name): IProgramme;
    public function setType(string $type): IProgramme;
    public function setNoTerms(int $noTerms): IProgramme;

    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}