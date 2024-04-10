<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ICourse;
use murica_bl\Dto\IRoom;
use murica_bl\Dto\IStudent;
use murica_bl\Dto\ISubject;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class Course extends Entity implements ICourse {
    //region Properties
    private ?ISubject $subject;
    private ?string $id;
    private ?int $capacity;
    private ?string $schedule;
    private ?string $term;
    private ?IRoom $room;
    //endregion

    //region Ctor
    public function __construct(ISubject $subject=null, string $id=null, string $capacity=null, string $schedule=null, $term=null, $room=null) {
        $this->subject = $subject;
        $this->id = isset($id) ? trim($id) : null;
        $this->capacity = $capacity;
        $this->schedule = isset($schedule) ? trim($schedule) : null;
        $this->term = isset($term) ? trim($term) : null;
        $this->room = $room;
    }
    //endregion

    //region Getters and Setters
    #[Override]
    public function getId(): ?string {
        return $this->id;
    }

    #[Override]
    public function getSubject(): ?ISubject {
        return $this->subject;
    }

    #[Override]
    public function getCapacity(): ?int {
        return $this->capacity;
    }

    #[Override]
    public function getSchedule(): ?string {
        return $this->schedule;
    }

    #[Override]
    public function getTerm(): ?string {
        return $this->term;
    }

    #[Override]
    public function getRoom(): ?IRoom {
        return $this->room;
    }

    #[Override]
    public function setId(string $id): ICourse {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function setSubject(ISubject $subject): ICourse {
        $this->subject = $subject;
        return $this;
    }

    #[Override]
    public function setCapacity(int $capacity): ICourse {
        $this->capacity = $capacity;
        return $this;
    }

    #[Override]
    public function setSchedule(string $schedule): ICourse {
        $this->schedule = $schedule;
        return $this;
    }

    #[Override]
    public function setTerm(string $term): ICourse {
        $this->term = $term;
        return $this;
    }

    #[Override]
    public function setRoom(IRoom $room): ICourse {
        $this->room = $room;
        return $this;
    }
    //endregion

    //region IStudent members
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->subject) || $this->subject->validate()) $errors .= "\nSubject cannot be empty or subject is invalid!";
        if (empty($this->id) || strlen($this->id) > 6) $errors .= "\nID cannot be empty or longer than 6 characters!";
        if (empty($this->capacity) || strlen($this->capacity) > 999) $errors .= "\nCapacity cannot be empty or bigger than 999!";
        if (empty($this->schedule) || !preg_match('/^[1-7]-([01]?[0-9]|2[0-3]):([0-5]?[0-9])-([01]?[0-9]|2[0-3]):([0-5]?[0-9])$/', $this->schedule)) $errors .= "\nSchedule cannot be empty or invalid format!";
        if (empty($this->term) || !preg_match('/^\d{4}\/\d{2}\/\d{1}$/', $this->term)) $errors .= "\nTerm is invalid!";
        if (empty($this->room) || $this->room.$this->validate()) $errors .= "\nRoom is invalid!";

        if (!empty($errors)) throw new ValidationException(ltrim($errors, "\n"));

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
            'subject' => $this->subject->jsonSerialize(),
            'id' => $this->id,
            'capacity' => $this->capacity,
            'schedule' => $this->schedule,
            'term' => $this->term,
            'room' => $this->room->jsonSerialize()
        ];
    }
    //endregion
}