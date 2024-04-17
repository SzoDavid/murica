<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\IRole;
use murica_bl\Dto\IStudent;
use murica_bl_impl\Models\Entity;
use Override;

class Role extends Entity implements IRole {
    //region Properties
    private ?bool $adminRole;
    private ?bool $teacherRole;
    private ?array $students;
    //endregion

    //region Ctor
    /**
     * @param bool|null $adminRole
     * @param bool|null $teacherRole
     * @param array|null $student
     */
    public function __construct(bool $adminRole=null, bool $teacherRole=null, array $student=null) {
        $this->adminRole = $teacherRole;
        $this->teacherRole = $teacherRole;
        $this->students = $student;
    }
    //endregion

    //region Getters
    #[Override]
    public function isAdmin(): ?bool {
        return $this->adminRole;
    }

    #[Override]
    public function isTeacher(): ?bool {
        return $this->teacherRole;
    }

    #[Override]
    public function getStudents(): ?array {
        return $this->students;
    }
    // endregion

    //region Setters
    #[Override]
    public function setAdminRole(bool $role): IRole {
        $this->adminRole = $role;
        return $this;
    }

    #[Override]
    public function setTeacherRole(bool $role): IRole {
        $this->teacherRole = $role;
        return $this;
    }

    #[Override]
    public function setStudents(array $students): IRole {
        $this->students = $students;
        return $this;
    }
    //endregion

    //region Public methods
    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array {
        $studentEntities = array();

        /* @var $student IStudent */
        foreach ($this->students as $student) {
            $studentEntities[] = [
                'user' => $student->getUser()->jsonSerialize(),
                'programme' => $student->getProgramme()->jsonSerialize(),
                'startTerm' => $student->getStartTerm(),
                'name' => $student->getProgramme()->getName(),
                'type' => $student->getProgramme()->getType()
            ];
        }

        return [
            'isAdmin' => $this->adminRole,
            'isTeacher' => $this->teacherRole,
            'student' => $studentEntities
        ];
    }
    //endregion
}