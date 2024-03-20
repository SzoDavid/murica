<?php

namespace murica_bl\DTO;

use murica_bl\DTO\_Interfaces\IUser;

class User implements IUser
{
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
    public function __construct(string $id, string $name, string $email, string $password, string $birthDate)
    {
        //TODO: validation
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->birthDate = $birthDate;
    }
    //endregion

    //region getters
    #[\Override]
    public function getId(): string
    {
        return $this->id;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function getEmail(): string
    {
        return $this->email;
    }

    #[\Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    #[\Override]
    public function getBirthDate(): string
    {
        return $this->birthDate;
    }
    //endregion

    //region Setters
    #[\Override]
    public function setId(string $id): IUser
    {
        // TODO: validation
        $this->id = $id;
        return $this;
    }

    #[\Override]
    public function setName(string $name): IUser
    {
        // TODO: validation
        $this->name = $name;
        return $this;
    }

    #[\Override]
    public function setEmail(string $email): IUser
    {
        // TODO: validation
        $this->email = $email;
        return $this;
    }

    #[\Override]
    public function setPassword(string $password): IUser
    {
        // TODO: validation
        $this->password = $password;
        return $this;
    }

    #[\Override]
    public function setBirthDate(string $birth_date): IUser
    {
        // TODO: validation
        $this->birthDate = $birth_date;
        return $this;
    }
    //endregion
}