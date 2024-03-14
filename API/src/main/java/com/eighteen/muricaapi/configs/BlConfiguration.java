package com.eighteen.muricaapi.configs;

import com.eighteen.muricabl.DAO.TextUserDAO;
import com.eighteen.muricabl.DAO._Interfaces.IUserDAO;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class BlConfiguration {
    @Bean
    IUserDAO userDAOImplementation() {
        return new TextUserDAO();
    }
}
