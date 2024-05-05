<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IProgramme;
use murica_bl_impl\Models\Entity;
use Override;

class Programme extends Entity implements IProgramme {
    //region Properties
    private ?string $name;
    private ?string $type;
    private ?int $noTerms;
    private ?int $noStudents;
    //endregion

    //region Ctor
    public function __construct(?string $name=null, ?string $type=null, ?int $noTerms=null, ?int $noStudents=null) {
        $this->name = isset($name) ? trim($name) : null;
        $this->type = isset($type) ? trim($type) : null;
        $this->noTerms = $noTerms;
        $this->noStudents = $noStudents;
    }
    //endregion

    //region Getters
    #[Override]
    public function getName(): ?string {
        return $this->name;
    }

    #[Override]
    public function getType(): ?string {
        return $this->type;
    }

    #[Override]
    public function getNoTerms(): ?int {
        return $this->noTerms;
    }

    #[Override]
    public function getNoStudents(): ?int {
        return $this->noStudents;
    }
    //endregion

    //region Setters
    #[Override]
    public function setName(string $name): IProgramme {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function setType(string $type): IProgramme {
        $this->type = $type;
        return $this;
    }

    #[Override]
    public function setNoTerms(int $noTerms): IProgramme {
        $this->noTerms = $noTerms;
        return $this;
    }

    #[Override]
    public function setNoStudents(?int $noStudents): IProgramme {
        $this->noStudents = $noStudents;
        return $this;
    }
    //endregion

    //region Public members
    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(): bool {
        $errors = "";

        if (empty($this->name) || strlen($this->name) > 50) $errors .= '\nName cannot be empty or longer than 50 characters!';
        if (empty($this->type) || strlen($this->type) > 10) $errors .= '\nType cannot be empty or longer than 10 characters!';
        if (!isset($this->noTerms) || $this->noTerms <= 0) $errors .= '\nNumber of terms must be a positive non-zero value!';

        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'noTerms' => $this->noTerms,
            'noStudents' => $this->noStudents
        ];
    }
    //endregion
}