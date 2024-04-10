<?php

namespace murica_bl_impl\Dto;

use DateTime;
use murica_bl\Dao\ICourseTeachDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ICourse;
use murica_bl\Dto\ICourseTeach;
use murica_bl\Dto\ISubject;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class CourseTeach extends Entity implements ICourseTeach {
    //region Properties
    private ?IUser $user;
    private ?ICourse $course;
    private ?ISubject $subject;
    //endregion

    //region Ctor
    /**
     * @param string|null $user
     * @param string|null $course
     * @param string|null $subject
     */
    public function __construct(IUser $user=null, ICourse $course=null, ISubject $subject=null) {
        $this->user = $user;
        $this->course = $course;
        $this->subject = $subject;
    }
    //endregion

    //region Getters
    #[Override]
    public function getUser(): ?IUser {
        return $this->user;
    }

    #[Override]
    public function getCourse(): ?ICourse {
        return $this->course;
    }

    #[Override]
    public function getSubject(): ?ISubject {
        return $this->subject;
    }
    // endregion

    //region Setters
    #[Override]
    public function setUser(IUser $user): ICourseTeach {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function setCourse(ICourse $course): ICourseTeach {
        $this->course = $course;
        return $this;
    }

    #[Override]
    public function setSubject(ISubject $subject): ICourseTeach {
        $this->subject = $subject;
        return $this;
    }
    //endregion

    //region Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->user) || $this->user->validate()) $errors .= '\nUser is invalid!';
        if (empty($this->course) || $this->course->validate()) $errors .= '\nCourse is invalid!';
        if (empty($this->subject) || $this->subject->validate()) $errors .= '\nSubject is invalid!';
        if (!empty($errors)) throw new ValidationException(ltrim($errors, '\n'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        return [
            'user' => $this->user->jsonSerialize(),
            'course' => $this->course->jsonSerialize(),
            'subject' => $this->subject->jsonSerialize()
        ];
    }
    //endregion
}