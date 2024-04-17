<?php

namespace murica_bl\Dto;

use murica_bl\Dto\Exceptions\ValidationException;

interface ICourseTeach {
    public function getUser(): ?IUser;
    public function getCourse(): ?ICourse;
    public function setUser(IUser $user): ICourseTeach;
    public function setCourse(ICourse $course): ICourseTeach;
    /**
     * @throws ValidationException
     */
    public function validate(): bool;
}