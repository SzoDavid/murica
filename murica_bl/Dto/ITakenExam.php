<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface ITakenExam {
    public function getStudent(): ?IStudent;
    public function getExam(): ?IExam;
    public function setStudent(IStudent $student): ITakenExam;
    public function setExam(IExam $exam): ITakenExam;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}