<?php

namespace murica_bl_impl\Dto;

use DateTime;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class User extends Entity implements IUser {
    //region Properties
    private ?string $id;
    private ?string $name;
    private ?string $email;
    private ?string $password;
    private ?string $birthDate;
    //endregion

    //region Ctor
    /**
     * @param string|null $id
     * @param string|null $name
     * @param string|null $email
     * @param string|null $password
     * @param string|null $birthDate
     */
    public function __construct(string $id=null, string $name=null, string $email=null, string $password=null, string $birthDate=null) {
        $this->id = isset($id) ? strtoupper(trim($id)) : null;
        $this->name = isset($name) ? trim($name) : null;
        $this->email = isset($email) ? trim($email) : null;
        $this->password = isset($password) ? trim($password) : null;
        $this->birthDate = isset($birthDate) ? trim($birthDate) : null;
    }
    //endregion

    //region Getters
    #[Override]
    public function getId(): ?string {
        return $this->id;
    }

    #[Override]
    public function getName(): ?string {
        return $this->name;
    }

    #[Override]
    public function getEmail(): ?string {
        return $this->email;
    }

    #[Override]
    public function getPassword(): ?string {
        return $this->password;
    }

    #[Override]
    public function getBirthDate(): ?string {
        return $this->birthDate;
    }
    // endregion

    //region Setters
    #[Override]
    public function setId(string $id): IUser {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function setName(string $name): IUser {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function setEmail(string $email): IUser {
        $this->email = $email;
        return $this;
    }

    #[Override]
    public function setPassword(string $password): IUser {
        $this->password = $password;
        return $this;
    }

    #[Override]
    public function setBirthDate(string $birthDate): IUser {
        $this->birthDate = $birthDate;
        return $this;
    }
    //endregion

    //region Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->id) || !preg_match('/^[A-Z0-9]{6}$/', $this->id)) $errors .= '\nID must contain letters and numbers only and must be 6 characters long!';
        if (empty($this->name) || strlen($this->name) > 50) $errors .= '\nName cannot be empty or longer than 50 characters!';
        if (empty($this->email) || filter_var($this->email, FILTER_VALIDATE_EMAIL)) $errors .= '\nEmail is invalid!';
        if (empty($this->password)) $errors .= '\nPassword is empty!';
        if (!empty($this->birthDate)) {
            $dateTime = DateTime::createFromFormat('Y-m-d', $this->birthDate);

            if (!$dateTime || $dateTime->format('Y-m-d') !== $this->birthDate) $errors .= '\nBirth date is invalid!';
        } else {
            $errors .= '\nBirth date is invalid!';
        }

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
            'email' => $this->email,
            'birth_date' => $this->birthDate
        ];
    }
    //endregion
}