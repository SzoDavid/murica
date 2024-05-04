<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface ICourse {
    public function getId(): ?string;
    public function getSubject(): ?ISubject;
    public function getCapacity(): ?int;
    public function getSchedule(): ?string;
    public function getTerm(): ?string;
    public function getRoom(): ?IRoom;
    public function getNumberOfParticipants(): ?int;
    public function setId(string $id): ICourse;
    public function setSubject(ISubject $subject): ICourse;
    public function setCapacity(int $capacity): ICourse;
    public function setSchedule(string $schedule): ICourse;
    public function setTerm(string $term): ICourse;
    public function setRoom(IRoom $room): ICourse;
    public function setNumberOfParticipants(?int $numberOfParticipants): ICourse;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}