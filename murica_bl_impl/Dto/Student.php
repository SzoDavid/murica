<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IStudent;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class Student extends Entity implements IStudent {
    //region Properties
    private ?IUser $user;
    private ?string $programmeName;
    private ?string $programmeType;
    private ?string $startTerm;
    //endregion

    //region Ctor
    public function __construct(IUser $user = null, string $programmeName= null, string $programmeType= null, string $startTerm= null) {
        $this->user = $user;
        $this->programmeName = isset($programmename) ? trim($programmeName) : null;
        $this->programmeType = isset($programmetype) ? trim($programmeType) : null;
        $this->startTerm = $startTerm;
    }
    //endregion

    //region Getters and Setters
    #[Override]
    public function getUser(): ?IUser {
        return $this->user;
    }

    #[Override]
    public function getProgrammeName(): ?string {
        return $this->programmeName;
    }

    #[Override]
    public function getProgrammeType(): ?string {
        return $this->programmeType;
    }

    #[Override]
    public function getStartTerm(): ?string {
        return $this->startTerm;
    }

    #[Override]
    public function setUser(IUser $user): IStudent {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function setProgrammeName(string $programName): IStudent {
        $this->programmeName = $programName;
        return $this;
    }

    #[Override]
    public function setProgrammeType(string $programmeType): IStudent {
        $this->programmeType = $programmeType;
        return $this;
    }

    #[Override]
    public function setStartTerm(string $startTerm): IStudent {
        $this->startTerm = $startTerm;
        return $this;
    }
    //endregion

    //region IStudent members
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->user) || $this->user->validate()) {
            $errors .= "\nID cannot be empty or user is invalid!";
        }
        if (empty($this->programmeType) || strlen($this->programmeType) > 10) {
            $errors .= "\nProgramme-type cannot be empty or longer than 10 characters!";
        }
        if (empty($this->programmeName) || strlen($this->programmeName) > 50) {
            $errors .= "\nProgramme-name cannot be empty or longer than 50 characters!";
        }
        if (empty($this->startTerm) || !preg_match('/^\d{4}\/\d{2}\/\d{1}$/', $this->startTerm)) {
            $errors .= "\nStart-term is invalid!";
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
            'user' => $this->user->jsonSerialize(),
            'programName' => $this->programmeName,
            'programType' => $this->programmeType,
            'startTerm' => $this->startTerm
            ];
    }
    //endregion

}