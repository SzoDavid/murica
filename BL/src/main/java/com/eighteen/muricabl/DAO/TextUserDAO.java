package com.eighteen.muricabl.DAO;

import com.eighteen.muricabl.DAO._Interfaces.IUserDAO;
import com.eighteen.muricabl.DTO.User;
import com.eighteen.muricabl.DTO._Interfaces.IUser;
import org.springframework.stereotype.Repository;
import org.springframework.beans.factory.annotation.Autowired;

import java.time.LocalDate;
import java.util.List;

@Repository
public class TextUserDAO implements IUserDAO {
    @Override
    public List<IUser> getAll() {
        return List.of(new User("YTWK3B", "Szobonya David", "szobonya.david@murica.com", "hash", LocalDate.now()));
    }
}
