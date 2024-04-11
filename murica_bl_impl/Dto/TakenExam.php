<?php

namespace murica_bl_impl\Dto;

use DateTime;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IExam;
use murica_bl\Dto\IStudent;
use murica_bl\Dto\ITakenExam;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class TakenExam extends Entity implements ITakenExam {
    //region Properties
    private ?IStudent $student;
    private ?IExam $exam;
    //endregion

    //region Ctor
    /**
     * @param IStudent|null $student
     * @param IExam|null $exam
     */
    public function __construct(IStudent $student=null, IExam $exam=null) {
        $this->student = $student;
        $this->exam = $exam;
    }
    //endregion

    //region Getters
    #[Override]
    public function getStudent(): ?IStudent {
        return $this->student;
    }

    #[Override]
    public function getExam(): ?IExam {
        return $this->exam;
    }
    // endregion

    //region Setters
    #[Override]
    public function setStudent(IStudent $student): ITakenExam {
        $this->student = $student;
        return $this;
    }

    #[Override]
    public function setExam(IExam $exam): ITakenExam {
        $this->exam = $exam;
        return $this;
    }
    //endregion

    //region Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->student) || $this->student->validate()) $errors .= '\nStudent is empty or invalid';
        if (empty($this->exam) || $this->exam->validate()) $errors .= '\nExam is empty or invalid';

        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        return [
            'student' => $this->student->jsonSerialize(),
            'exam' => $this->exam->jsonSerialize()
        ];
    }
    //endregion
}