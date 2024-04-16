<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IRoom;
use murica_bl_impl\Models\Entity;
use Override;

class Room extends Entity implements IRoom {
    //region Properties
    private ?string $id;
    private ?int $capacity;
    //endregion

    //region Ctor
    public function __construct(string $id = null, int $capacity = null) {
        $this->id = isset($id) ? strtoupper(trim($id)) : null;
        $this->capacity = $capacity;
    }
    //endregion

    //region Getters
    #[Override]
    public function getId(): ?string {
        return $this->id;
    }

    #[Override]
    public function getCapacity(): ?int {
        return $this->capacity;
    }
    //endregion

    //region Setters
    #[Override]
    public function setId(string $id): IRoom {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function setCapacity(int $capacity): IRoom {
        $this->capacity = $capacity;
        return $this;
    }
    //endregion

    //region IRoom members
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->id) || !preg_match('/^[A-Z]{2}-\d{3}$/', $this->id)) {
            $errors .= "\nID must consist of two capital letters followed by a hyphen and three digits!";
        }
        if (empty($this->capacity) || $this->capacity > 999 || $this->capacity < 1) {
            $errors .= "\nCapacity cannot be empty or larger than 999!";
        }
        if (!empty($errors)) {
            throw new ValidationException(ltrim($errors, "\n"));
        }
        return true;
    }
    //endregion

    //region JsonSerializable members
    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'capacity' => $this->capacity
        ];
    }
    //endregion

}