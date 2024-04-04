<?php

namespace murica_bl_impl\Dto;

use DateTime;
use murica_bl\Dto\Exceptions\ValidationException;
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
    public function __construct(string $id=null, string $name=null, string $email=null, string $password=null, string $birthDate=null) {
        $this->id = isset($id) ? strtoupper(trim($id)) : null;
        $this->name = isset($name) ? trim($name) : null;
        $this->email = isset($email) ? trim($email) : null;
        $this->password = isset($password) ? trim($password) : null;
        $this->birthDate = isset($birthDate) ? trim($birthDate) : null;
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

    #[Override]
    public function validate(): bool {
        $errors = "";
        if (!preg_match('/^[A-Z0-9]{6}$/', $this->id)) $errors .= '\nID must contain letters and numbers only and must be 6 characters long!';
        if (strlen($this->name) > 50) $errors .= '\nName cannot be longer than 50 characters';
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) $errors .= '\nEmail is invalid';
        if (empty($this->password)) $errors .= '\nPassword is empty';

        $dateTime = DateTime::createFromFormat('Y-m-d', $this->birthDate);

        if (!$dateTime || $dateTime->format('Y-m-d') !== $this->birthDate) $errors .= '\nBirth date is invalid';

        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
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