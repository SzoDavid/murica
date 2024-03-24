<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class User extends Entity implements IUser {
    //region Properties
    private string $id;
    private string $name;
    private string $email;
    private string $password;
    private string $birthDate;
    //endregion

    //region constructor
    /**
     * @param string $id
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $birthDate
     */
    public function __construct(string $id, string $name, string $email, string $password, string $birthDate) {
        //TODO: validation
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->birthDate = $birthDate;
    }
    //endregion

    //region getters
    #[Override]
    public function getId(): string {
        return $this->id;
    }

    #[Override]
    public function getName(): string {
        return $this->name;
    }

    #[Override]
    public function getEmail(): string {
        return $this->email;
    }

    #[Override]
    public function getPassword(): string {
        return $this->password;
    }

    #[Override]
    public function getBirthDate(): string {
        return $this->birthDate;
    }
    //endregion

    //region JsonSerializable members
    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'birth_date' => $this->birthDate
        ];
    }
    //endregion
}