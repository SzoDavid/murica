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
    private ?string $programmename;
    private ?string $programmetype;
    private ?string $startterm;
    //endregion

    //region Ctor
    /**
     * @param string $id
     * @param string $programmename
     * @param string $programmetype
     * @param string $startterm
     */
    public function __construct(IUser $id = null, string $programmename= null, string $programmetype= null, string $startterm= null) {
        $this->user = isset($id) ? strtoupper(trim($id)) : null;
        $this->programmename = isset($programmename) ? trim($programmename) : null;
        $this->programmetype = isset($programmetype) ? trim($programmetype) : null;
        $this->startterm = $startterm;
    }
    //endregion

    //region Getters and Setters
    #[Override]
    public function getUser(): ?IUser {
        return $this->user;
    }
    #[Override]
    public function getProgrammename(): ?string {
        return $this->programmename;
    }
    #[Override]
    public function getProgrammetype(): ?string {
        return $this->programmetype;
    }
    #[Override]
    public function getStartterm(): ?string {
        return $this->startterm;
    }
    #[Override]
    public function setUser(IUser $user): IStudent {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function setProgrammeName(string $programname): IStudent {
        $this->programmename = $programname;
        return $this;
    }

    #[Override]
    public function setProgrammeType(string $type): IStudent {
        $this->programmetype = $type;
        return $this;
     }

    #[Override]
    public function setStartTerm(string $startterm): IStudent {
        $this->startterm = $startterm;
        return $this;
     }
    //endregion

    //region IStudent members
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->user) || strlen($this->user) > 6) {
            $errors .= "\nID cannot be empty or longer than 6 characters!";
        }
        if (empty($this->programmetype) || strlen($this->programmetype) > 10) {
            $errors .= "\nProgramm-type cannot be empty or longer than 10 characters!";
        }
        if (empty($this->programmename) || strlen($this->programmename) > 50) {
            $errors .= "\nProgramme-name cannot be empty or longer than 50 characters!";
        }
        if (empty($this->startterm) || !preg_match('/^\d{4}\/\d{2}\/\d{1}$/', $this->startterm)) {
            $errors .= "\nStart-term must be in the format 'YYYY/MM/D'!";
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
            'id' => $this->user,
            'programName' => $this->programmename,
            'programType' => $this->programmetype,
            'startTerm' => $this->startterm
            ];
    }
    //endregion

}