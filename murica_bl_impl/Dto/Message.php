<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IMessage;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class Message extends Entity implements IMessage {
    //region Properties
    private ?IUser $user;
    private ?string $subject;
    private ?string $content;
    private ?string $dateTime;
    //endregion

    //region Ctor
    /**
     * @param IUser|null $user
     * @param string|null $subject
     * @param string|null $content
     * @param string|null $dateTime
     */
    public function __construct(?string $dateTime=null, ?string $subject=null, ?string $content=null, ?IUser $user=null) {
        $this->user = $user;
        $this->subject = isset($subject) ? trim($subject) : null;
        $this->content = isset($content) ? trim($content) : null;
        $this->dateTime = isset($dateTime) ? trim($dateTime) : null;
    }
    //endregion

    //region Getters
    #[Override]
    public function getUser(): ?IUser {
        return $this->user;
    }

    #[Override]
    public function getSubject(): ?string {
        return $this->subject;
    }

    #[Override]
    public function getContent(): ?string {
        return $this->content;
    }

    #[Override]
    public function getDateTime(): ?string {
        return $this->dateTime;
    }
    //endregion

    //region Setters
    #[Override]
    public function setUser(IUser $user): IMessage {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function setSubject(string $subject): IMessage {
        $this->subject = $subject;
        return $this;
    }

    #[Override]
    public function setContent(string $content): IMessage {
        $this->content = $content;
        return $this;
    }

    #[Override]
    public function setDateTime(string $dateTime): IMessage {
        $this->dateTime = $dateTime;
        return $this;
    }
    //endregion


    //region Public members
    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(): bool {
        $errors = "";

        $this->user->validate();

        if (empty($this->subject) || strlen($this->subject) > 256) $errors .= '\nSubject cannot be empty or longer than 256 characters!';
        if (empty($this->content)) $errors .= '\nContent cannot be empty!';

        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    #[Override]
    public function jsonSerialize(): array {
        return [
            'user' => $this->user->jsonSerialize(),
            'subject' => $this->subject,
            'content' => $this->content,
            'dateTime' => $this->dateTime
        ];
    }
    //endregion

}