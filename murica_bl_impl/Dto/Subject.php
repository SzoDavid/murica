<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ISubject;
use murica_bl_impl\Models\Entity;
use Override;

class Subject extends Entity implements ISubject {

    //region Properties
    private ?string $id;
    private ?string $name;
    private ?string $approval;
    private ?int $credit;
    private ?string $type;
    //endregion

    //region constructor

    public function __construct(string $id=null, string $name=null, string $approval=null, int $credit=null, string $type=null) {
        $this->id = isset($id) ? strtoupper(trim($id)) : null;
        $this->name = isset($name) ? (trim($name)) : null;
        $this->approval = $approval;
        $this->credit = $credit;
        $this->type = isset($type) ? (trim($type)) : null;
    }
    //endregion

    //region Getters
    public function getId(): ?string {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getApproval(): ?string {
        return $this->approval;
    }

    public function getCredit(): ?int {
        return $this->credit;
    }

    public function getType(): ?string {
        return $this->type;
    }
    //endregion

    //region Setters
    #[Override]
    public function setId(string $id): ISubject {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function setName(string $name): ISubject {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function setApproval(int $approval): ISubject {
        $this->approval = $approval;
        return $this;
    }

    #[Override]
    public function setCredit(int $credit): ISubject {
        $this->credit = $credit;
        return $this;
    }

    #[Override]
    public function setType(string $type): ISubject {
        $this->type = $type;
        return $this;
    }
    //endregion


    //region  Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->id) || !preg_match('/^[a-zA-Z]{2}\d{3}[eg]$/', $this->id)) $errors .= '\nID must contain letters and numbers only and must be 6 characters long!';
        if (empty($this->name) || strlen($this->name) > 50) $errors .= '\nName cannot be empty or longer than 50 characters!';
        if (empty($this->approval) || (($this->approval != 0) && ($this->approval != 1))) $errors .= '\nApproval must be 0 or 1!';
        if (empty($this->credit) || ($this->credit < 0)) $errors .= '\nCredit is empty or negative!';
        if (empty($this->type) || strlen($this->type) > 50) $errors .= '\nType cannot be empty or longer than 20 characters!';


        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'approval' => $this->approval,
            'credit' => $this->credit,
            'type' => $this->type
        ];
    }
    //endregion
}