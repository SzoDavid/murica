package com.eighteen.muricabl.DTO._Interfaces;

import java.time.LocalDate;

/**
 * Interface to represent the user model
 */
public interface IUser {
    //region Getters
    String getId();
    String getName();
    String getEmail();
    String getPasswordHash();
    LocalDate getBirthDate();
    //endregion

    //region Setters
    IUser setId(String id);
    IUser setName(String name);
    IUser setEmail(String email);
    IUser setPasswordHash(String passwordHash);
    IUser setBirthDate(LocalDate birthDate);
    //endregion
}
