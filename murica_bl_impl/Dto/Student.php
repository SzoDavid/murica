<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IProgramme;
use murica_bl\Dto\IStudent;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class Student extends Entity implements IStudent {
    //region Properties
    private ?IUser $user;
    private ?IProgramme $programme;
    private ?string $startTerm;
    //endregion

    //region Ctor
    public function __construct(IUser $user = null, IProgramme $programme= null, string $startTerm= null) {
        $this->user = $user;
        $this->programme = $programme;
        $this->startTerm = $startTerm;
    }
    //endregion

    //region Getters and Setters
    #[Override]
    public function getUser(): ?IUser {
        return $this->user;
    }

    #[Override]
    public function getProgramme(): ?IProgramme {
        return $this->programme;
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
    public function setProgramme(IProgramme $programme): IStudent {
        $this->programme = $programme;
        return $this;
    }

    #[Override]
    public function setStartTerm(string $startTerm): IStudent {
        $this->startTerm = $startTerm;
        return $this;
    }
    //endregion

    //region Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        // TODO refactor validation to return false if no issues was found or a string with all the issues
        if (empty($this->user) || !$this->user->validate()) $errors .= "\nID cannot be empty or user is invalid!";
        if (empty($this->programme) || !$this->programme->validate()) $errors .= "\nProgramme is empty or invalid!";
        if (empty($this->startTerm) || !preg_match('/^\d{4}\/\d{2}\/\d{1}$/', $this->startTerm)) $errors .= "\nStart-term is invalid!";

        if (!empty($errors)) throw new ValidationException(ltrim($errors, "\n"));

        return true;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array {
        return [
            'user' => $this->user->jsonSerialize(),
            'programme' => $this->programme->jsonSerialize(),
            'startTerm' => $this->startTerm
            ];
    }
    //endregion

}