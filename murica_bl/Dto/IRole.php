<?php

namespace murica_bl\Dto;

interface IRole {
    public function isAdmin(): ?bool;
    public function isTeacher(): ?bool;
    public function getStudents(): ?array;

    public function setAdminRole(bool $role): IRole;
    public function setTeacherRole(bool $role): IRole;
    public function setStudents(array $students): IRole;

}