<?php

namespace murica_bl_impl\Dto;

use murica_bl\Dto\IAdmin;
use murica_bl\Dto\IUser;
use murica_bl_impl\Models\Entity;
use Override;

class Admin extends Entity implements IAdmin {
    //region Properties
    private IUser $user;
    //endregion

    //region Ctor
    /**
     * @param IUser $user
     */
    public function __construct(IUser $user) {
        $this->user = $user;
    }
    //endregion

    //region Getters
    #[Override]
    public function getUser(): IUser {
        return $this->user;
    }
    //endregion

    //region Setters
    #[Override]
    public function setUser(IUser $user): IAdmin {
        $this->user = $user;
        return $this;
    }
    //endregion

    //region Public methods
    /**
     * @inheritDoc
     */
    #[Override]
    public function validate(): bool {
        return $this->user->validate();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function jsonSerialize(): mixed {
        return $this->user->jsonSerialize();
    }
    //endregion
}