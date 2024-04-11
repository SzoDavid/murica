<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface ITakenCourse {
    public function getStudent(): ?IStudent;
    public function getCourse(): ?ICourse;
    public function getGrade(): ?int;
    public function isApproved(): ?bool;
    public function setStudent(IStudent $student): ITakenCourse;
    public function setCourse(ICourse $course): ITakenCourse;
    public function setGrade(int $grade): ITakenCourse;
    public function setApproved(bool $approved): ITakenCourse;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}