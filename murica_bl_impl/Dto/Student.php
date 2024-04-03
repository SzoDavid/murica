<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IStudent;
use murica_bl_impl\Models\Entity;
use Override;

class Student extends Entity implements IStudent {
    //region Properties
    private string $id;
    private string $programmename;
    private string $programmetype;
    private string $startterm;
    //endregion
    //region Ctor
    /**
     * @param string $id
     * @param string $programmename
     * @param string $programmetype
     * @param string $startterm
     */
    public function __construct(string $id, string $programmename, string $programmetype, string $startterm)
    {
        $this->id = $id;
        $this->programmename = $programmename;
        $this->programmetype = $programmetype;
        $this->startterm = $startterm;
    }
    //endregion
    //region Getters and Setters
    #[Override]
    public function getUserId(): string
    {
        return $this->id;
    }
    #[Override]
    public function getProgrammename(): string
    {
        return $this->programmename;
    }
    #[Override]
    public function getProgrammetype(): string
    {
        return $this->programmetype;
    }
    #[Override]
    public function getStartterm(): string
    {
        return $this->startterm;
    }
    #[Override]
    public function setUserId(string $userid): IStudent {
        $this->id = $userid;
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
    //iStudent members
    #[Override]
    public function validate(): bool
    {
        $errors = "";
        if (empty($this->id) || strlen($this->id) > 6) $errors .= '\nID cannot be empty or longer than 6 characters!';
        if (empty($this->programmetype) || strlen($this->programmetype) > 10) $errors .= '\nProgramm-type cannot be empty or longer than 10 characters!';
        if (empty($this->programmename) || strlen($this->programmename) > 50) $errors .= '\nProgramm-name cannot be empty or longer than 50 characters!';
        if (empty($this->startterm) || strlen($this->startterm) > 9) $errors .= '\nStart-term cannot be empty or longer than 9 characters!';
        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));
        return true;
    }
    //endregion
    //region JsonSerializable members
    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'programmename' => $this->programmename,
            'programmetype' => $this->programmetype,
            'startterm' => $this->startterm,
            ];
    }
    //endregion

}