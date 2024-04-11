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
    //endregion

    //region Ctor
    /**
     * @param string|null $user
     * @param string|null $course
     */
    public function __construct(IUser $user=null, ICourse $course=null) {
        $this->user = $user;
        $this->course = $course;
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
    //endregion

    //region Public methods
    #[Override]
    public function validate(): bool {
        $errors = "";
        if (empty($this->user) || $this->user->validate()) $errors .= '\nUser is invalid!';
        if (empty($this->course) || $this->course->validate()) $errors .= '\nCourse is invalid!';
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
        ];
    }
    //endregion
}