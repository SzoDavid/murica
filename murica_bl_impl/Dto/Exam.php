<?php

namespace murica_bl_impl\Dto;

use DateTime;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IExam;
use murica_bl\Dto\IRoom;
use murica_bl\Dto\ISubject;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class Exam extends Entity implements IExam {
    //region Properties
    private ?ISubject $subject;
    private ?string $id;
    private ?string $startTime;
    private ?string $endTime;
    private ?int $noStudents;
    private ?IUser $teacher;
    private ?IRoom $room;
    //endregion

    //region Ctor
    public function __construct(?ISubject $subject=null, ?string $id=null, ?string $startTime=null, ?string $endTime=null, ?int $noStudents=null, ?IUser $teacher=null, ?IRoom $room=null) {
        $this->subject = $subject;
        $this->id = isset($id) ? strtoupper(trim($id)) : null;
        $this->startTime = isset($startTime) ? trim($startTime) : null;
        $this->endTime = isset($endTime) ? trim($endTime) : null;
        $this->noStudents = $noStudents;
        $this->teacher = $teacher;
        $this->room = $room;
    }
    //endregion

    //region Getters
    #[Override]
    public function getSubject(): ?ISubject {
        return $this->subject;
    }

    #[Override]
    public function getId(): ?string {
        return $this->id;
    }

    #[Override]
    public function getStartTime(): ?string {
        return $this->startTime;
    }

    #[Override]
    public function getEndTime(): ?string {
        return $this->endTime;
    }

    #[Override]
    public function getNoStudents(): ?int {
        return $this->noStudents;
    }

    #[Override]
    public function getTeacher(): ?IUser {
        return $this->teacher;
    }

    #[Override]
    public function getRoom(): ?IRoom {
        return $this->room;
    }
    // endregion

    //region Setters
    #[Override]
    public function setSubject(ISubject $subject): IExam {
        $this->subject = $subject;
        return $this;
    }

    #[Override]
    public function setId(string $id): IExam {
        $this->id = $id;
        return $this;
    }

    #[Override]
    public function setStartTime(string $startTime): IExam {
        $this->startTime = $startTime;
        return $this;
    }

    #[Override]
    public function setEndTime(string $endTime): IExam {
        $this->endTime = $endTime;
        return $this;
    }

    #[Override]
    public function setNoStudents(int $noStudents): IExam {
        $this->noStudents = $noStudents;
        return $this;
    }

    #[Override]
    public function setTeacher(IUser $teacher): IExam {
        $this->teacher = $teacher;
        return $this;
    }

    #[Override]
    public function setRoom(IRoom $room): IExam {
        $this->room = $room;
        return $this;
    }
    //endregion

    //region Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        // TODO refactor validation to return false if no issues was found or a string with all the issues
        $this->subject->validate();
        $this->teacher->validate();
        $this->room->validate();
        if (empty($this->id) || strlen($this->id) !== 6) $errors .= '\nID must contain 6 characters!';
        if (!empty($this->startTime)) {
            $dateTime = DateTime::createFromFormat('Y-m-d H:i', $this->startTime);

            if (!$dateTime || $dateTime->format('Y-m-d H:i') !== $this->startTime) $errors .= '\nStart time format is invalid!';
        } else {
            $errors .= '\nStart time is invalid!';
        }
        if (!empty($this->endTime)) {
            $dateTime = DateTime::createFromFormat('Y-m-d H:i', $this->endTime);

            if (!$dateTime || $dateTime->format('Y-m-d H:i') !== $this->endTime) $errors .= '\nEnd time format is invalid!';
        } else {
            $errors .= '\nEnd time is invalid!';
        }
        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        return [
            'subject' => $this->subject,
            'subjectName' => $this->subject->getName() . ' [' . $this->subject->getId() . ']',
            'id' => $this->id,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'teacher' => $this->teacher,
            'capacity' => $this->room->getCapacity(),
            'room' => $this->room,
            'roomId' => $this->room->getId(),
            'noStudents' => $this->noStudents
        ];
    }
    //endregion
}