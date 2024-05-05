<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface IExam {
    public function getSubject(): ?ISubject;
    public function getId(): ?string;
    public function getStartTime(): ?string;
    public function getEndTime(): ?string;
    public function getNoStudents(): ?int;
    public function getTeacher(): ?IUser;
    public function getRoom(): ?IRoom;
    public function setSubject(ISubject $subject): IExam;
    public function setId(string $id): IExam;
    public function setStartTime(string $startTime): IExam;
    public function setEndTime(string $endTime): IExam;
    public function setNoStudents(int $noStudents): IExam;
    public function setTeacher(IUser $teacher): IExam;
    public function setRoom(IRoom $room): IExam;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}