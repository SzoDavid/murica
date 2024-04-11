<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ICourse;
use murica_bl\Dto\IStudent;
use murica_bl\Dto\ITakenCourse;
use murica_bl_impl\Models\Entity;
use Override;

class TakenCourse extends Entity implements ITakenCourse {
    //region Properties
    private ?IStudent $student;
    private ?ICourse $course;
    private ?int $grade;
    private ?bool $approved;
    //endregion

    //region Ctor
    /**
     * @param IStudent|null $student
     * @param ICourse|null $course
     * @param int|null $grade
     * @param bool|null $approved
     */
    public function __construct(IStudent $student=null, ICourse $course=null, int $grade=null, bool $approved=null) {
        $this->student = $student;
        $this->course = $course;
        $this->grade = $grade;
        $this->approved = $approved;
    }
    //endregion

    //region Getters
    #[Override]
    public function getStudent(): ?IStudent {
        return $this->student;
    }

    #[Override]
    public function getCourse(): ?ICourse {
        return $this->course;
    }

    #[Override]
    public function getGrade(): ?int {
        return $this->grade;
    }

    #[Override]
    public function isApproved(): ?bool {
        return $this->approved;
    }
    // endregion

    //region Setters
    #[Override]
    public function setStudent(IStudent $student): ITakenCourse {
        $this->student = $student;
        return $this;
    }

    #[Override]
    public function setCourse(ICourse $course): ITakenCourse {
        $this->course = $course;
        return $this;
    }

    #[Override]
    public function setGrade(int $grade): ITakenCourse {
        $this->grade = $grade;
        return $this;
    }

    #[Override]
    public function setApproved(bool $approved): ITakenCourse {
        $this->approved = $approved;
        return $this;
    }
    //endregion

    //region Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->student) || $this->student->validate()) $errors .= '\nStudent is invalid!';
        if (empty($this->course) || $this->course->validate()) $errors .= '\nCourse is invalid!';
        if (empty($this->grade) || ($this->grade > 5) || ($this->grade < 0)) $errors .= '\nGrade is invalid!';
        if (empty($this->approved)) $errors .= '\nApproved is empty!';
        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        return [
            'student' => $this->student->jsonSerialize(),
            'course' => $this->course->jsonSerialize(),
            'grade' => $this->grade,
            'approved' => $this->approved
        ];
    }
    //endregion
}