<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IExam;
use murica_bl\Dto\IStudent;
use murica_bl\Dto\ITakenExam;
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
        // TODO refactor validation to return false if no issues was found or a string with all the issues
        $this->student->validate();
        $this->exam->validate();

        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        return [
            'student' => $this->student->jsonSerialize(),
            'exam' => $this->exam->jsonSerialize(),
            'subjectId' => $this->exam->getSubject()->getId(),
            'subjectName' => $this->exam->getSubject()->getName(),
            'examId' => $this->exam->getId(),
            'startTime' => $this->exam->getStartTime(),
            'endTime' => $this->exam->getEndTime(),
            'roomId' => $this->exam->getRoom()->getId(),
            'capacity' => $this->exam->getRoom()->getCapacity(),
            'noStudents' => $this->exam->getNoStudents(),
            'userId' => $this->student->getUser()->getId(),
            'userName' => $this->student->getUser()->getName(),
            'userProgramme' => $this->student->getProgramme()->getName() . '/' . $this->student->getProgramme()->getType()
        ];
    }
    //endregion
}