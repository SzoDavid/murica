<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IMessage {
    public function getUser(): ?IUser;
    public function getSubject(): ?string;
    public function getContent(): ?string;
    public function getDateTime(): ?string;
    public function setUser(IUser $user): IMessage;
    public function setSubject(string $subject): IMessage;
    public function setContent(string $content): IMessage;
    public function setDateTime(string $dateTime): IMessage;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}
