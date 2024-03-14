package com.eighteen.muricaapi;

import com.eighteen.muricabl.DAO._Interfaces.IUserDAO;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.domain.EntityScan;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

@SpringBootApplication
@EntityScan(basePackages = "com.eighteen.muricabl.DTO")
@RestController
public class MuricaApiApplication {

	private IUserDAO _userDAO;

	@Autowired
	public MuricaApiApplication(IUserDAO userDAO) {
		this._userDAO = userDAO;
	}

	@GetMapping("/")
	public String home() {
		return _userDAO.getAll().toString();
	}

	public static void main(String[] args) {
		SpringApplication.run(MuricaApiApplication.class, args);
	}

}
