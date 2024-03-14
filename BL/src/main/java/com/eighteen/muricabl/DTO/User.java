package com.eighteen.muricabl.DTO;

import com.eighteen.muricabl.DTO._Interfaces.IUser;

import java.time.LocalDate;

public class User implements IUser {
    //region Properties
    private String _id;
    private String _name;
    private String _email;
    private String _passwordHash;
    private LocalDate _birthDate;
    //endregion


    public User(String id, String name, String email, String passwordHash, LocalDate birthDate) {
        this._id = id;
        this._name = name;
        this._email = email;
        this._passwordHash = passwordHash;
        this._birthDate = birthDate;
    }

    //region Getters
    @Override
    public String getId() {
        return null;
    }

    @Override
    public String getName() {
        return null;
    }

    @Override
    public String getEmail() {
        return null;
    }

    @Override
    public String getPasswordHash() {
        return null;
    }

    @Override
    public LocalDate getBirthDate() {
        return null;
    }
    //endregion

    //region Setters
    @Override
    public IUser setId(String id) {
        return null;
    }

    @Override
    public IUser setName(String name) {
        return null;
    }

    @Override
    public IUser setEmail(String email) {
        return null;
    }

    @Override
    public IUser setPasswordHash(String passwordHash) {
        return null;
    }

    @Override
    public IUser setBirthDate(LocalDate birthDate) {
        return null;
    }
    //endregion
}
