package com.eighteen.muricabl.DAO._Interfaces;

import com.eighteen.muricabl.DTO._Interfaces.IUser;

import java.util.List;

/**
 * Data access object for the user model.
 */
public interface IUserDAO {
    List<IUser> getAll();
}
